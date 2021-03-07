<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\se_item\Entity\Item;

/**
 * Class GoodsReceiptInsertEventSubscriber.
 *
 * Update the items with the goods receipt number after it has been saved.
 *
 * @package Drupal\se_goods_receipt\EventSubscriber
 */
class GoodsReceiptEventSubscriber implements GoodsReceiptEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineEntityPresave',
      HookEventDispatcherInterface::ENTITY_INSERT => 'goodsReceiptItemsInsert',
      // HookEventDispatcherInterface::ENTITY_DELETE => '', // @todo delete.
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemLineEntityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'se_goods_receipt') {
      return;
    }

    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$entity->bundle()];
    foreach ($entity->{$bundleFieldType . '_lines'} as $index => $itemLine) {
      if (!empty($itemLine->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($itemLine->target_id)) {
          if ($item->se_it_serial->value !== $itemLine->serial) {
            $newItem = $item->createDuplicate();
            $newItem
              ->set('se_it_serial', $itemLine->serial)
              ->set('se_it_item_ref', $item->id())
              ->save();

            $entity->{$bundleFieldType . '_lines'}[$index]->target_id = $newItem->id();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function goodsReceiptItemsInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'se_goods_receipt') {
      return;
    }

    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$entity->bundle()];
    foreach ($entity->{$bundleFieldType . '_lines'} as $itemLine) {
      /** @var \Drupal\se_item\Entity\Item $item */
      if ($item = Item::load($itemLine->target_id)) {
        if ($item->bundle() !== 'se_stock') {
          continue;
        }
        $item
          ->set('se_it_goods_receipt_ref', $entity->id())
          ->set('se_it_purchase_order_ref', $entity->se_gr_purchase_order_ref->target_id)
          ->set('se_it_cost_price', $itemLine->price)
          ->save();
      }
    }
  }

}
