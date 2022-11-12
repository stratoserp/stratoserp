<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_customer\Entity\CustomerInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_invoice\Entity\InvoiceInterface;
use Drupal\se_quote\Entity\QuoteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InvoiceController.
 *
 *  Returns responses for Invoice routes.
 */
class InvoiceController extends ControllerBase {

  /**
   * @var \Drupal\Core\Datetime\DateFormatter*/
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Render\Renderer*/
  protected $renderer;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel*/
  protected $stratosLogger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    $instance->stratosLogger = $container->get('logger.channel.stratoserp');
    return $instance;
  }

  /**
   * Displays a Invoice revision.
   *
   * @param int $se_invoice_revision
   *   The Invoice revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_invoice_revision) {
    $se_invoice = $this->entityTypeManager()->getStorage('se_invoice')
      ->loadRevision($se_invoice_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_invoice');

    return $view_builder->view($se_invoice);
  }

  /**
   * Page title callback for a Invoice revision.
   *
   * @param int $se_invoice_revision
   *   The Invoice revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_invoice_revision) {
    $se_invoice = $this->entityTypeManager()->getStorage('se_invoice')
      ->loadRevision($se_invoice_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_invoice->label(),
      '%date' => $this->dateFormatter->format($se_invoice->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Invoice.
   *
   * @param \Drupal\se_invoice\Entity\InvoiceInterface $se_invoice
   *   A Invoice object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InvoiceInterface $se_invoice) {
    $account = $this->currentUser();
    $langcode = $se_invoice->language()->getId();
    $langname = $se_invoice->language()->getName();
    $languages = $se_invoice->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_invoice_storage = $this->entityTypeManager()->getStorage('se_invoice');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_invoice->label(),
      ]);
    }
    else {
      $this->t('Revisions for %title', [
        '%title' => $se_invoice->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all invoice revisions") || $account->hasPermission('administer invoice entities')));
    $delete_permission = (($account->hasPermission("delete all invoice revisions") || $account->hasPermission('administer invoice entities')));

    $rows = [];

    $vids = $se_invoice_storage->revisionIds($se_invoice);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_invoice\Entity\InvoiceInterface $revision */
      $revision = $se_invoice_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode)
        && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_invoice->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_invoice.revision', [
            'se_invoice' => $se_invoice->id(),
            'se_invoice_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_invoice->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.se_invoice.translation_revert', [
                'se_invoice' => $se_invoice->id(),
                'se_invoice_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_invoice.revision_revert', [
                'se_invoice' => $se_invoice->id(),
                'se_invoice_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_invoice.revision_delete', [
                'se_invoice' => $se_invoice->id(),
                'se_invoice_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['se_invoice_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-invoice-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Provides the entity form for invoice creation from a quote.
   *
   * @param \Drupal\se_quote\Entity\QuoteInterface $source
   *   Source entity to copy data from.
   *
   * @return array
   *   An entity submission form.
   */
  public function fromQuote(QuoteInterface $source): array {
    $entity = $this->createInvoiceFromQuote($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides the entity form for creation from timekeeping entries.
   *
   * @param \Drupal\se_customer\Entity\CustomerInterface $source
   *   The source entity.
   *
   * @return array
   *   An entity submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function fromTimekeeping(CustomerInterface $source): array {
    $entity = $this->createInvoiceFromTimekeeping($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides the entity for invoice creation from timekeeping entries.
   *
   * @param \Drupal\se_customer\Entity\CustomerInterface $customer
   *   The source entity.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   An entity ready for the submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createInvoiceFromTimekeeping(CustomerInterface $customer): Invoice {

    $invoice = Invoice::create([
      'bundle' => 'se_invoice',
    ]);

    // Retrieve a list of non billed timekeeping entries for this customer.
    $query = $this->entityTypeManager()->getStorage('se_timekeeping')->getQuery();

    $query->condition('se_cu_ref', $customer->id())
      ->condition('se_billed', TRUE, '<>')
      ->condition('se_billable', TRUE)
      ->condition('se_amount', 0, '>');
    $entityIds = $query->execute();

    $total = 0;
    $lines = [];

    // Loop through the timekeeping entries and setup invoice lines.
    foreach ($entityIds as $entityId) {
      /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
      if ($timekeeping = $this->entityTypeManager()
        ->getStorage('se_timekeeping')
        ->load($entityId)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = $timekeeping->se_it_ref->entity) {
          $price = (int) $item->se_sell_price->value;
          $line = [
            'target_type' => 'se_timekeeping',
            'target_id' => $timekeeping->id(),
            'quantity' => round($timekeeping->se_amount->value / 60, 2),
            'note' => $timekeeping->se_comment->value,
            'format' => $timekeeping->se_comment->format,
            'price' => $price,
          ];
          $lines[] = $line;
          $total += $line['quantity'] * $line['price'];
        }
        else {
          $this->stratosLogger->error('No matching item for entry @id', ['@id' => $timekeeping->id()]);
        }
      }
    }

    $invoice->se_cu_ref = $customer;
    $invoice->se_item_lines = $lines;
    $invoice->setTotal((int) $total);

    return $invoice;
  }

  /**
   * Provides the entity for creating an invoice from a quote.
   *
   * @param \Drupal\se_quote\Entity\QuoteInterface $quote
   *   The source entity.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   An entity ready for the submission form.
   */
  public function createInvoiceFromQuote(QuoteInterface $quote): Invoice {
    $invoice = Invoice::create([
      'bundle' => 'se_invoice',
    ]);

    $total = 0;

    // @todo Make this a service.
    /**
     * @var int $index
     * @var \Drupal\se_item_line\Plugin\Field\FieldType\ItemLineType $item
     */
    foreach ($quote->se_item_lines as $item) {
      $invoice->se_item_lines->appendItem($item->getValue());
    }

    $invoice->se_cu_ref = $quote->se_cu_ref;
    $invoice->se_co_ref = $quote->se_co_ref ?? NULL;
    $invoice->se_qu_ref = $quote;
    $invoice->se_total = $total;

    return $invoice;
  }

}
