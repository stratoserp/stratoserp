<?php

declare(strict_types=1);

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Item saving.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
interface ItemLineEntityEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Calculate the total based on the item lines values.
   *
   * Uses a specific event priority to ensure that the total is
   * calculated in time for other events.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function itemLineEntityPresave(EntityPresaveEvent $event);

}
