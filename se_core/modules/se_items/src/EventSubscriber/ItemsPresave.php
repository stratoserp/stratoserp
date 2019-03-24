<?php

namespace Drupal\se_items\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
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
   * When one of the node types that has items in it is saved,
   * calculate the total of the items for saving.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function itemsPreSave(EntityPresaveEvent $event) {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'paragraph' && $entity->bundle() === 'se_items') {
      foreach ($entity->field_it_line_item as $index => $item) {
        // Manually set the serial number field, if its a stock item.
        if ($item = Item::load($item->target_id)) {
          if (!empty($item->field_it_serial->value) && empty($entity->field_it_serial->value)) {
            $entity->field_it_serial->value = $item->field_it_serial->value;
          }
        }
      }
    }
  }

}