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
interface ItemLineNodeEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Process line items when a node with them is saved.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function itemLineNodePresave(EntityPresaveEvent $event);

}
