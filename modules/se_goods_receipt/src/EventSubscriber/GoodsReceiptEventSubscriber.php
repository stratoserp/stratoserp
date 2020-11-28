<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
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
class GoodsReceiptEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineNodePresave',
      HookEventDispatcherInterface::ENTITY_INSERT => 'goodsReceiptItemsInsert',
    ];
  }

  /**
   * Process items on goods receipt saving.
   *
   * For goods receipts, We need to make new stock items for everything that
   * has a serial number, and then update the list of items with the new
   * stock item ids.
   *
   * @todo Config option to generate serial numbers if blank?
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemLineNodePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($entity->bundle() !== 'se_goods_receipt') {
      return;
    }

    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
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
   * Update item details.
   *
   * For goods receipts, we can update the items with the goods receipt number
   * After the goods receipt has been saved.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function goodsReceiptItemsInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($entity->bundle() !== 'se_goods_receipt') {
      return;
    }

    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
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
