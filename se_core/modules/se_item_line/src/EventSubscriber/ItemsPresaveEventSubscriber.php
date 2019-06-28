<?php

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemsPresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemsPreSave',
    ];
  }

  /**
   * The 'serial' field at the node level is really just display only,
   * except for in goods receipt, where its used to enter serial numbers.
   *
   * On save, if that field is empty, fill it out.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function itemsPreSave(EntityPresaveEvent $event) {
    $entity = $event->getEntity();

    if (!in_array($entity->bundle(), ErpCore::ITEMS_BUNDLE_MAP)) {
      return;
    }

    foreach ($entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_items'} as $index => $item) {
      if (empty($item->serial) && $item = Item::load($item->target_id)) {
        $entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_items'}[$index]->serial =
          $item->field_it_serial->value;
      }
    }
  }

}