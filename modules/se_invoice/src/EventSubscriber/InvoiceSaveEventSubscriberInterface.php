<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Invoice saving.
 *
 * @package Drupal\se_invoice\EventSubscriber
 */
interface InvoiceSaveEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Add the total of this invoice to the amount the business owes.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function invoiceInsert(EntityInsertEvent $event);

  /**
   * Add the total of this invoice to the amount the business owes.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   */
  public function invoiceUpdate(EntityUpdateEvent $event);

  /**
   * Reduce the business balance by the amount of the old invoice.
   *
   * This needs to be done in case the amount changes on the saving
   * of this invoice.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function invoiceAdjust(EntityPresaveEvent $event);

  /**
   * Reduce the business balance by the amount of the invoice.
   *
   * This needs to be done or the amount owed by the business will be wrong.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event we are working with.
   */
  public function invoiceDelete(EntityDeleteEvent $event);

}
