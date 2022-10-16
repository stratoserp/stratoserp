<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Invoice saving.
 *
 * @package Drupal\se_invoice\EventSubscriber
 */
interface InvoiceEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Reduce the customer balance by the amount of the old invoice.
   *
   * This needs to be done in case the amount changes on the saving
   * of this invoice.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function invoicePreAction(EntityPresaveEvent $event): void;

  /**
   * Update the customer balance after an invoice is saved.
   */
  public function invoiceInsert(EntityInsertEvent $event): void;

  /**
   * Update the customer balance after an invoice is updated.
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void;

}
