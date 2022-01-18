<?php

declare(strict_types=1);

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;

/**
 * Class ItemLinePresaveEventSubscriber.
 *
 * When an entity with item lines is saved, recalculate the total.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class ItemLineEntityEventSubscriber implements ItemLineEntityEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemLineEntityPresave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemLineEntityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if (!isset($entity->se_item_lines)) {
      return;
    }

    \Drupal::service('se_item_line.total_update')->calculateTotal($entity);
  }

}
