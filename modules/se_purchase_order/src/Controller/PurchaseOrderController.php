<?php

namespace Drupal\se_purchase_order\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_purchase_order\Entity\PurchaseOrder;
use Drupal\se_purchase_order\Entity\PurchaseOrderInterface;
use Drupal\stratoserp\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PurchaseOrderController.
 *
 *  Returns responses for PurchaseOrder routes.
 */
class PurchaseOrderController extends ControllerBase {

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
   * Displays a PurchaseOrder revision.
   *
   * @param int $se_purchase_order_revision
   *   The PurchaseOrder revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_purchase_order_revision) {
    $se_purchase_order = $this->entityTypeManager()->getStorage('se_purchase_order')
      ->loadRevision($se_purchase_order_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_purchase_order');

    return $view_builder->view($se_purchase_order);
  }

  /**
   * Page title callback for a PurchaseOrder revision.
   *
   * @param int $se_purchase_order_revision
   *   The PurchaseOrder revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_purchase_order_revision) {
    $se_purchase_order = $this->entityTypeManager()->getStorage('se_purchase_order')
      ->loadRevision($se_purchase_order_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_purchase_order->label(),
      '%date' => $this->dateFormatter->format($se_purchase_order->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a PurchaseOrder.
   *
   * @param \Drupal\se_purchase_order\Entity\PurchaseOrderInterface $se_purchase_order
   *   A PurchaseOrder object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(PurchaseOrderInterface $se_purchase_order) {
    $account = $this->currentUser();
    $se_purchase_order_storage = $this->entityTypeManager()->getStorage('se_purchase_order');

    $langcode = $se_purchase_order->language()->getId();
    $langname = $se_purchase_order->language()->getName();
    $languages = $se_purchase_order->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_purchase_order->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_purchase_order->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all purchase order revisions") || $account->hasPermission('administer purchase order entities')));
    $delete_permission = (($account->hasPermission("delete all purchase order revisions") || $account->hasPermission('administer purchase order entities')));

    $rows = [];

    $vids = $se_purchase_order_storage->revisionIds($se_purchase_order);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_purchase_order\Entity\PurchaseOrderInterface $revision */
      $revision = $se_purchase_order_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_purchase_order->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_purchase_order.revision', [
            'se_purchase_order' => $se_purchase_order->id(),
            'se_purchase_order_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_purchase_order->toLink($date)->toString();
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
              Url::fromRoute('entity.se_purchase_order.translation_revert', [
                'se_purchase_order' => $se_purchase_order->id(),
                'se_purchase_order_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_purchase_order.revision_revert', [
                'se_purchase_order' => $se_purchase_order->id(),
                'se_purchase_order_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_purchase_order.revision_delete', [
                'se_purchase_order' => $se_purchase_order->id(),
                'se_purchase_order_revision' => $vid,
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

    $build['se_purchase_order_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Provides the entity form for purchase order creation from a quote.
   *
   * @paeram \Drupal\Core\Entity\EntityInterface $source
   *   Source entity to copy data from.
   *
   * @return array
   *   An entity submission form.
   */
  public function fromQuote(EntityInterface $source) {
    $entity = $this->createPurchaseOrderFromQuote($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provide the entity for creating a purchase order from a quote.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_purchase_order\Entity\PurchaseOrder
   *   An entity submission form.
   */
  public function createPurchaseOrderFromQuote(EntityInterface $source) {

    $destination = PurchaseOrder::create([
      'bundle' => 'se_purchase_order',
    ]);

    $total = 0;
    $sourceFieldType = 'se_' . Constants::SE_ITEM_LINE_BUNDLES[$source->getEntityTypeId()];
    $bundleFieldType = 'se_' . Constants::SE_ITEM_LINE_BUNDLES[$destination->getEntityTypeId()];

    // @todo Make this a service?
    /**
     * @var int $index
     * @var \Drupal\se_item_line\Plugin\Field\FieldType\ItemLineType $item
     */
    foreach ($source->{$sourceFieldType . '_lines'} as $item) {
      $destination->{$bundleFieldType . '_lines'}->appendItem($item->getValue());
    }

    $destination->se_bu_ref->target_id = $source->se_bu_ref->target_id;
    $destination->se_bu_ref->target_type = $source->se_bu_ref->target_type;
    $destination->se_co_ref->target_id = $source->se_co_ref->target_id;
    $destination->{$bundleFieldType . '_quote_ref'}->target_id = $source->id();
    $destination->{$bundleFieldType . '_total'} = $total;

    return $destination;
  }

}
