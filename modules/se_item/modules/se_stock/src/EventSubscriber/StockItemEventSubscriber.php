<?php

declare(strict_types=1);

namespace Drupal\se_stock\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\se_item\Entity\Item;

/**
 * Class StockItemPresaveEventSubscriber.
 *
 * Create 'parent' items for stock items if they don't already exist.
 *
 * @package Drupal\se_stock\EventSubscriber
 */
class StockItemEventSubscriber implements StockItemEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'stockItemPresave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function stockItemPresave(EntityPresaveEvent $event): void {
    $item = $event->getEntity();
    if (!$item instanceof Item) {
      return;
    }

    if ($item->bundle() !== 'se_stock') {
      return;
    }

    $item = \Drupal::service('se_stock.service')->ensureItem($item);
  }

}
