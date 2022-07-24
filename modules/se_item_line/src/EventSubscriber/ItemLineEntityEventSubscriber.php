<?php

declare(strict_types=1);

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;

/**
 * Class ItemLinePresaveEventSubscriber.
 *
 * Calculate the total based on the item lines values.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class ItemLineEntityEventSubscriber implements ItemLineEntityEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => [
        'itemLineEntityPresave', 100,
      ],
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

    \Drupal::service('se_item_line.service')->calculateTotal($entity);
  }

}
