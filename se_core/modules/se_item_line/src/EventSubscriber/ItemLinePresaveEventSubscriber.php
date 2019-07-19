<?php

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ItemLinePresaveEventSubscriber
 *
 * When a node with item lines is saved, recalculate the total of the node.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class ItemLinePresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineNodePresave',
    ];
  }

  /**
   * When one of the node types that has items in it is saved,
   * adjust the prices for storage and calculate the total for the node.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function itemLineNodePresave(EntityPresaveEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    if (($entity = $event->getEntity()) && ($entity->getEntityTypeId() !== 'node')) {
      return;
    }

    // Check that its a node type which has items.
    if (!array_key_exists($entity->bundle(), ErpCore::ITEMS_BUNDLE_MAP)) {
      return;
    }

    $total = 0;
    $bundle_field_type = 'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()];

    // Loop through the item lines to calculate total
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      if (empty($item_line->serial)) {
        /** @var Item $item */
        if (($item = Item::load($item_line->target_id)) && $item->bundle() === 'se_stock') {
          $entity->{$bundle_field_type . '_lines'}[$index]->serial = $item->field_it_serial->value;
        }
      }

      $total += $item_line->quantity * $item_line->price;
    }

    $entity->{$bundle_field_type . '_total'}->value = $total;
  }

}
