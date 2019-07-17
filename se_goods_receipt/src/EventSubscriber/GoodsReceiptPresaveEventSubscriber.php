<?php

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\Entity\Node;

class GoodsReceiptPresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemsPresave',
    ];
  }

  /**
   * For goods receipts, We need to make new stock items for everything that
   * has a serial number, and then update the list of items with the new
   * stock item ids.
   *
   * @param EntityPresaveEvent $event
   *
   * TODO - Config option to generate serial numbers if blank?
   */
  public function itemsPresave(EntityPresaveEvent $event) {
    /** @var Node $entity */
    if (($entity = $event->getEntity()) && ($entity->getEntityTypeId() !== 'node')) {
      return;
    }

    if ($entity->bundle() !== 'se_goods_receipt') {
      return;
    }

    $bundle_field_type = 'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()];
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      if (!empty($item_line->serial)) {
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
