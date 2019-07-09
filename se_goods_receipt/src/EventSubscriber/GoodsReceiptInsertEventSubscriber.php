<?php

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\Entity\Node;

class GoodsReceiptInsertEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemsInsert',
    ];
  }

  /**
   * For goods receipts, we can update the items with the goods receipt number
   * After the goods receipt has been saved.
   *
   * @param EntityInsertEvent $event
   *
   */
  public function itemsInsert(EntityInsertEvent $event) {
    /** @var Node $entity */
    if (($entity = $event->getEntity()) && ($entity->getEntityTypeId() !== 'node')) {
      return;
    }

    if ($entity->bundle() !== 'se_goods_receipt') {
      return;
    }

    $bundle_field_type = 'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()];
    foreach ($entity->{$bundle_field_type . '_items'} as $index => $item_line) {
      /** @var Item $item */
      if ($item = Item::load($item_line->target_id)) {
        if ($item->bundle() !== 'se_stock') {
          continue;
        }
        $item->set('field_it_goods_receipt_ref', $entity->id());
        $item->set('field_it_purchase_order_ref', $entity->field_gr_purchase_order_ref->target_id);
        $item->save();
      }
    }
  }

}
