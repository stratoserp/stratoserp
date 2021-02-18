<?php

declare(strict_types=1);

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;

/**
 * Class ItemLinePresaveEventSubscriber.
 *
 * When a node with item lines is saved, recalculate the total of the node.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class ItemLineNodeEventSubscriber implements ItemLineNodeEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineNodePresave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemLineNodePresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if (!array_key_exists($entity->getEntityTypeId(), ErpCore::ITEM_LINE_ENTITY_BUNDLE_MAP)) {
      return;
    }

    \Drupal::service('se_item_line.total_update')->calculateTotal($entity);

  }

}
