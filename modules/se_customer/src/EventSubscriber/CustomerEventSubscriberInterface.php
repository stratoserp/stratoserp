<?php

declare(strict_types=1);

namespace Drupal\se_customer\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Customer Event subscriber for invoice and payment changes.
 *
 * @package Drupal\se_customer\EventSubscriber
 */
interface CustomerEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * If an invoice or payment was inserted, update customer balance.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function entityInsert(EntityInsertEvent $event): void;

  /**
   * If an invoice or payment was updated, update customer balance.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   */
  public function entityUpdate(EntityUpdateEvent $event): void;

  /**
   * If an invoice or payment was deleted, update customer balance.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event we are working with.
   */
  public function entityDelete(EntityDeleteEvent $event): void;

}
