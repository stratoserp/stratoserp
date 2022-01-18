<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\stratoserp\Constants;
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
    if (!($entity instanceof GoodsReceipt)) {
      return;
    }

    foreach ($entity->se_item_lines as $index => $itemLine) {
      if (!empty($itemLine->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($itemLine->target_id)) {
          if ($item->se_serial->value !== $itemLine->serial) {
            $newItem = $item->createDuplicate();
            $newItem
              ->set('se_serial', $itemLine->serial)
              ->set('se_it_ref', $item->id())
              ->save();

            $entity->se_item_lines[$index]->target_id = $newItem->id();
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
    if (!($entity instanceof GoodsReceipt)) {
      return;
    }

    foreach ($entity->se_item_lines as $itemLine) {
      /** @var \Drupal\se_item\Entity\Item $item */
      if ($item = Item::load($itemLine->target_id)) {
        if ($item->bundle() !== 'se_stock') {
          continue;
        }
        $item
          ->set('se_gr_ref', $entity->id())
          ->set('se_po_ref', $entity->se_po_ref->target_id)
          ->set('se_cost_price', $itemLine->price)
          ->save();
      }
    }
  }

}
