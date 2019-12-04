<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GoodsReceiptPresaveEventSubscriber.
 *
 * Handle goods being received and create new stock items for the ones
 * that have serial numbers.
 *
 * @package Drupal\se_goods_receipt\EventSubscriber
 */
class GoodsReceiptPresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineNodePresave',
    ];
  }

  /**
   * For goods receipts, We need to make new stock items for everything that
   * has a serial number, and then update the list of items with the new
   * stock item ids.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *
   *   TODO - Config option to generate serial numbers if blank?
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

    $bundle_field_type = 'field_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      if (!empty($item_line->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($item_line->target_id)) {
          if ($item->field_it_serial->value !== $item_line->serial) {
            $new_item = $item->createDuplicate();
            $new_item->field_it_serial->value = $item_line->serial;
            $new_item->field_it_item_ref->target_id = $item->id();
            $new_item->save();

            $entity->{$bundle_field_type . '_lines'}[$index]->target_id = $new_item->id();
          }
        }
      }
    }
  }

}
