<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GoodsReceiptInsertEventSubscriber.
 *
 * Update the items with the goods receipt number after it has been saved.
 *
 * @package Drupal\se_goods_receipt\EventSubscriber
 */
class GoodsReceiptInsertEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemsInsert',
    ];
  }

  /**
   * Update item details.
   *
   * For goods receipts, we can update the items with the goods receipt number
   * After the goods receipt has been saved.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function itemsInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($entity->bundle() !== 'se_goods_receipt') {
      return;
    }

    $bundle_field_type = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      /** @var \Drupal\se_item\Entity\Item $item */
      if ($item = Item::load($item_line->target_id)) {
        if ($item->bundle() !== 'se_stock') {
          continue;
        }
        $item->set('se_it_goods_receipt_ref', $entity->id());
        $item->set('se_it_purchase_order_ref', $entity->se_gr_purchase_order_ref->target_id);
        $item->save();
      }
    }
  }

}
