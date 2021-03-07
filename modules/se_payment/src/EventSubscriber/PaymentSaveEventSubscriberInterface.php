<?php

declare(strict_types=1);

namespace Drupal\se_payment\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PaymentSaveEventSubscriber.
 *
 * For each invoice in the payment, mark it as paid.
 *
 * @see \Drupal\se_invoice\EventSubscriber\InvoiceSaveEventSubscriber
 *
 * @package Drupal\se_payment\EventSubscriber
 */
interface PaymentSaveEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * When a payment is saved, mark all invoices listed as paid.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentInsert(EntityInsertEvent $event);

  /**
   * Whn a payment is updated, make all invoices as paid.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentUpdate(EntityUpdateEvent $event);

  /**
   * When a payment is about to be saved, change existing payment lines.
   *
   * This is in case the payment is saved and has had some lines removed.
   * Without this, those invoices would then still show as paid.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentPresave(EntityPresaveEvent $event);

  /**
   * When a payment is about to be deleted, change existing payment lines.
   *
   * Without this, those invoices would then still show as paid.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentDelete(EntityDeleteEvent $event);

}
