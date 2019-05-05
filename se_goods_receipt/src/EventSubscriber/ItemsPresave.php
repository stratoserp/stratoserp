<?php

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemsPresave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemsPreSave',
    ];
  }

  /**
   * For goods receipts, We need to make new stock items for everything that
   * has a serial number, and then update the list of items with the new
   * stock item ids.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function itemsPreSave(EntityPresaveEvent $event) {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'paragraph' && $entity->bundle() === 'se_items') {
      if (!empty($entity->field_it_serial->value) &&
        $item = Item::load($entity->field_it_line_item->target_id)) {
        if ($item->field_it_serial->value !== $entity->field_it_serial->value) {
          $new_item = $item->createDuplicate();
          $new_item->field_it_serial->value = $entity->field_it_serial->value;
          $new_item->save();
          $entity->field_it_line_item->target_id = $new_item->id();
        }
      }
    }
  }

}
