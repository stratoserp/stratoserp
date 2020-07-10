<?php

declare(strict_types=1);

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ItemLinePresaveEventSubscriber.
 *
 * When a node with item lines is saved, recalculate the total of the node.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class ItemLinePresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineNodePresave',
    ];
  }

  /**
   * Process line items when a node with them is saved.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function itemLineNodePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    // Check that its a node type which has items.
    if (!array_key_exists($entity->bundle(), ErpCore::ITEM_LINE_NODE_BUNDLE_MAP)) {
      return;
    }

    $total = 0;
    $bundle_field_type = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    // Loop through the item lines to calculate total.
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      // We don't need to do anything with comments.
      if ($item_line->target_type === 'comment') {
        continue;
      }

      if (empty($item_line->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if (($item = Item::load($item_line->target_id)) && $item->bundle() === 'se_stock') {
          $entity->{$bundle_field_type . '_lines'}[$index]->serial = $item->se_it_serial->value;
        }
      }

      $total += $item_line->quantity * $item_line->price;
    }

    $entity->{$bundle_field_type . '_total'}->value = $total;
  }

}
