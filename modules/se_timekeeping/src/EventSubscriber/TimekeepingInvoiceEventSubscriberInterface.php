<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Item saving.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
interface TimekeepingInvoiceEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * When an invoice is saved, check if there are any timekeeping entries.
   *
   * When there are, mark them as billed.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The Event to handle.
   *
   * @see \Drupal\se_invoice\Entity\Invoice
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInvoiceInsert(EntityInsertEvent $event);

  /**
   * When an invoice is updated, check if there are any timekeeping entries.
   *
   * When there are, mark them as billed.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The Event to handle.
   *
   * @see \Drupal\se_invoice\Entity\Invoice
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInvoiceUpdate(EntityUpdateEvent $event);

  /**
   * Mark timekeeping entries as unbilled before saving.
   *
   * This needs to be done when an invoice is deleted so they
   * will need to be available to be billed again.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The Event to handle.
   *
   * @see \Drupal\se_invoice\Entity\Invoice
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInvoiceDelete(EntityDeleteEvent $event);

}
