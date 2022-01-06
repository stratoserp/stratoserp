<?php

declare(strict_types=1);

namespace Drupal\se_payment\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_payment\Entity\Payment;
use Drupal\se_payment\Entity\PaymentInterface;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PaymentController.
 *
 *  Returns responses for Payment routes.
 */
class PaymentController extends ControllerBase {

  use PaymentTrait;

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
   * Displays a Payment revision.
   *
   * @param int $se_payment_revision
   *   The Payment revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_payment_revision) {
    $se_payment = $this->entityTypeManager()->getStorage('se_payment')
      ->loadRevision($se_payment_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_payment');

    return $view_builder->view($se_payment);
  }

  /**
   * Page title callback for a Payment revision.
   *
   * @param int $se_payment_revision
   *   The Payment revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_payment_revision) {
    $se_payment = $this->entityTypeManager()->getStorage('se_payment')
      ->loadRevision($se_payment_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_payment->label(),
      '%date' => $this->dateFormatter->format($se_payment->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Payment.
   *
   * @param \Drupal\se_payment\Entity\PaymentInterface $se_payment
   *   A Payment object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(PaymentInterface $se_payment) {
    $account = $this->currentUser();
    $se_payment_storage = $this->entityTypeManager()->getStorage('se_payment');

    $langcode = $se_payment->language()->getId();
    $langname = $se_payment->language()->getName();
    $languages = $se_payment->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_payment->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_payment->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all payment revisions") || $account->hasPermission('administer payment entities')));
    $delete_permission = (($account->hasPermission("delete all payment revisions") || $account->hasPermission('administer payment entities')));

    $rows = [];

    $vids = $se_payment_storage->revisionIds($se_payment);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_payment\Entity\PaymentInterface $revision */
      $revision = $se_payment_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_payment->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_payment.revision', [
            'se_payment' => $se_payment->id(),
            'se_payment_revision' => $vid,
          ]));
        }
        else {
          $link = $se_payment->toLink($date)->toString();
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
              Url::fromRoute('entity.se_payment.translation_revert', [
                'se_payment' => $se_payment->id(),
                'se_payment_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_payment.revision_revert', [
                'se_payment' => $se_payment->id(),
                'se_payment_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_payment.revision_delete', [
                'se_payment' => $se_payment->id(),
                'se_payment_revision' => $vid,
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

    $build['se_payment_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Provides the entity submission form for payment creation from an invoice.
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
  public function fromInvoice(EntityInterface $source): array {
    $entity = $this->createPaymentFromInvoice($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides the entity for creating a payment from an invoice.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source invoice entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_payment\Entity\Payment
   *   An entity ready for the submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createPaymentFromInvoice(EntityInterface $source): EntityInterface {

    $payment = Payment::create([
      'bundle' => 'se_payment',
    ]);

    $paymentType = \Drupal::service('config.factory')->get('se_payment.settings')->get('default_payment_term');
    if (!$paymentTerm = Term::load($paymentType)) {
      \Drupal::messenger()->addWarning('Unable to load default payment term.');
    }

    $business = \Drupal::service('se_business.service')->lookupBusiness($source);

    $total = 0;
    $query = \Drupal::entityQuery('se_invoice');
    $group = $query->orConditionGroup()
      ->condition('se_status_ref', \Drupal::service('se_invoice.service')->getOpenTerm()->id())
      ->notExists('se_status_ref');

    $query->condition('se_bu_ref', $business->id())
      ->condition($group);

    $entityIds = $query->execute();

    // Build a list of outstanding invoices and make payment lines out of them.
    $lines = [];
    foreach ($entityIds as $id) {
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      if ($invoice = $this->entityTypeManager()->getStorage('se_invoice')->load($id)) {
        $outstandingAmount = $invoice->getInvoiceBalance();
        $line = [
          'target_id' => $invoice->id(),
          'target_type' => 'se_invoice',
          'amount' => $outstandingAmount,
          'payment_type' => $paymentTerm->id() ?? '',
        ];
        $lines[] = $line;
        $total += $outstandingAmount;
      }
    }

    $payment->se_bu_ref = $business;
    $payment->se_pa_lines = $lines;
    $payment->se_pa_total = $total;

    return $payment;
  }

}
