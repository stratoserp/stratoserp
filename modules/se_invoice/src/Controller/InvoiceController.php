<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_invoice\Entity\InvoiceInterface;
use Drupal\stratoserp\ErpCore;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InvoiceController.
 *
 *  Returns responses for Invoice routes.
 */
class InvoiceController extends ControllerBase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
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
    $se_invoice_storage = $this->entityTypeManager()->getStorage('se_invoice');

    $langcode = $se_invoice->language()->getId();
    $langname = $se_invoice->language()->getName();
    $languages = $se_invoice->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_invoice->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_invoice->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all invoice revisions") || $account->hasPermission('administer invoice entities')));
    $delete_permission = (($account->hasPermission("delete all invoice revisions") || $account->hasPermission('administer invoice entities')));

    $rows = [];

    $vids = $se_invoice_storage->revisionIds($se_invoice);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_invoice\InvoiceInterface $revision */
      $revision = $se_invoice_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_invoice->getRevisionId()) {
          $link = $this->l($date, new Url('entity.se_invoice.revision', [
            'se_invoice' => $se_invoice->id(),
            'se_invoice_revision' => $vid,
          ]));
        }
        else {
          $link = $se_invoice->link($date);
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
    ];

    return $build;
  }

  /**
   * Provides the entity submission form for invoice creation from a quote.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   Source entity to copy data from.
   *
   * @return array
   *   An entity submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function add(EntityInterface $source) {

    $destination = Invoice::create([
      'bundle' => 'se_invoice',
    ]);

    $total = 0;
    $sourceFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$source->getEntityTypeId()];
    $destFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$destination->getEntityTypeId()];

    // @todo Make this a service.
    /**
     * @var int $index
     * @var \Drupal\se_item_line\Plugin\Field\FieldType\ItemLineType $item
     */
    foreach ($source->{$sourceFieldType . '_lines'} as $item) {
      $destination->{$destFieldType . '_lines'}->appendItem($item->getValue());
    }

    $destination->se_bu_ref->target_id = $source->se_bu_ref->target_id;
    $destination->se_bu_ref->target_type = $source->se_bu_ref->target_type;
    $destination->se_co_ref->target_id = $source->se_co_ref->target_id;
    $destination->{$destFieldType . '_quote_ref'}->target_id = $source->id();
    $destination->{$destFieldType . '_total'} = $total;

    return $this->entityFormBuilder()->getForm($destination);
  }

  /**
   * Provides the node submission form for creation from timekeeping entries.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function timekeeping(EntityInterface $source): array {
    $entity = $this->createInvoiceFromTimekeeping($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides the node submission form for creation from timekeeping entries.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A build entity ready to display in a form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createInvoiceFromTimekeeping(EntityInterface $source): array {

    $destination = Invoice::create([
      'bundle' => 'se_invoice',
    ]);

    // Retrieve a list of non billed timekeeping entries for this business.
    $query = \Drupal::entityQuery('comment');

    // @todo something wrong here.
    if ($source->getEntityTypeId() !== 'se_business') {
      if (!$source->se_bu_ref->target_id) {
        return $this->entityFormBuilder()->getForm($destination);
      }
    }

    $query->condition('comment_type', 'se_timekeeping')
      ->condition('se_bu_ref', $source->id())
      ->condition('se_tk_billed', TRUE, '<>')
      ->condition('se_tk_billable', TRUE)
      ->condition('se_tk_amount', 0, '>');
    $entityIds = $query->execute();

    $total = 0;
    $lines = [];
    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$source->getEntityTypeId()];

    // Loop through the timekeeping entries and setup invoice lines.
    foreach ($entityIds as $entityId) {
      /** @var \Drupal\comment\Entity\Comment $comment */
      if ($comment = $this->entityTypeManager()->getStorage('comment')->load($entityId)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = $comment->se_tk_item->entity) {
          $price = (int) $item->se_it_sell_price->value;
          $line = [
            'target_type' => 'comment',
            'target_id' => $comment->id(),
            'quantity' => round($comment->se_tk_amount->value / 60, 2),
            'notes' => $comment->se_tk_comment->value,
            'format' => $comment->se_tk_comment->format,
            'price' => $price,
          ];
          $lines[] = $line;
          $total += $line['quantity'] * $line['price'];
        }
        else {
          \Drupal::logger('se_timekeeping')->error('No matching item for entry @cid', ['@cid' => $comment->id()]);
        }
      }
    }

    $destination->{$bundleFieldType . '_lines'} = $lines;
    $destination->{$bundleFieldType . '_total'} = $total;

    return $destination;
  }

}
