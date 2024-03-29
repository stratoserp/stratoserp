<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\se_goods_receipt\Entity\GoodsReceiptInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GoodsReceiptController.
 *
 *  Returns responses for Goods receipt routes.
 */
class GoodsReceiptController extends ControllerBase {

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
   * Displays a Goods receipt revision.
   *
   * @param int $se_goods_receipt_revision
   *   The Goods receipt revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_goods_receipt_revision) {
    $se_goods_receipt = $this->entityTypeManager()->getStorage('se_goods_receipt')
      ->loadRevision($se_goods_receipt_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_goods_receipt');

    return $view_builder->view($se_goods_receipt);
  }

  /**
   * Page title callback for a Goods receipt revision.
   *
   * @param int $se_goods_receipt_revision
   *   The Goods receipt revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_goods_receipt_revision) {
    $se_goods_receipt = $this->entityTypeManager()->getStorage('se_goods_receipt')
      ->loadRevision($se_goods_receipt_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_goods_receipt->label(),
      '%date' => $this->dateFormatter->format($se_goods_receipt->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Goods receipt.
   *
   * @param \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface $se_goods_receipt
   *   A Goods receipt object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(GoodsReceiptInterface $se_goods_receipt) {
    $account = $this->currentUser();

    $langcode = $se_goods_receipt->language()->getId();
    $langname = $se_goods_receipt->language()->getName();
    $languages = $se_goods_receipt->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_goods_receipt_storage = $this->entityTypeManager()->getStorage('se_goods_receipt');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_goods_receipt->label(),
      ]);
    }
    else {
      $this->t('Revisions for %title', [
        '%title' => $se_goods_receipt->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all goods receipt revisions") || $account->hasPermission('administer goods receipt entities')));
    $delete_permission = (($account->hasPermission("delete all goods receipt revisions") || $account->hasPermission('administer goods receipt entities')));

    $rows = [];

    $vids = $se_goods_receipt_storage->revisionIds($se_goods_receipt);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface $revision */
      $revision = $se_goods_receipt_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_goods_receipt->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_goods_receipt.revision', [
            'se_goods_receipt' => $se_goods_receipt->id(),
            'se_goods_receipt_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_goods_receipt->toLink($date)->toString();
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
              Url::fromRoute('entity.se_goods_receipt.translation_revert', [
                'se_goods_receipt' => $se_goods_receipt->id(),
                'se_goods_receipt_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_goods_receipt.revision_revert', [
                'se_goods_receipt' => $se_goods_receipt->id(),
                'se_goods_receipt_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_goods_receipt.revision_delete', [
                'se_goods_receipt' => $se_goods_receipt->id(),
                'se_goods_receipt_revision' => $vid,
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

    $build['se_goods_receipt_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-goods-receipt-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Provide entity submission form for receiving goods for a purchase order.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   Source entity to copy data from.
   *
   * @return array
   *   An entity submission form.
   */
  public function fromPurchaseOrder(EntityInterface $source): array {
    $entity = $this->createGoodsReceiptFromPurchaseOrder($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides the entity for goods receipt creation from a purchase order.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An entity ready for the submission form.
   */
  public function createGoodsReceiptFromPurchaseOrder(EntityInterface $source) {
    $goodsReceipt = GoodsReceipt::create([
      'bundle' => 'se_goods_receipt',
    ]);

    // For each item in the purchase order, create the qty
    // number of fields for serial number entry.
    foreach ($source->se_item_lines as $itemLine) {
      // @todo ensure we're using the non-serialised item here?
      $itemCount = $itemLine->quantity;
      for ($count = 0; $count < $itemCount; $count++) {
        // This section assumes we are doing serial numbers for everything.
        $values = $itemLine->getValue();
        $values['quantity'] = 1;
        $goodsReceipt->se_item_lines->appendItem($values);
      }
    }

    $goodsReceipt->se_cu_ref = $source->se_cu_ref;
    $goodsReceipt->se_co_ref = $source->se_co_ref;
    $goodsReceipt->se_po_ref->target_id = $source->id();

    return $goodsReceipt;
  }

}
