<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_invoice\Entity\Invoice;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * When an invoice is saved, adjust the customer
 * balance by the amount of the invoice.
 *
 * @see \Drupal\se_payment\EventSubscriber\PaymentSaveEventSubscriber
 *
 * @package Drupal\se_invoice\EventSubscriber
 */
class InvoiceSaveEventSubscriber implements InvoiceSaveEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => [
        'invoicePresave', 25,
      ],
      EntityHookEvents::ENTITY_INSERT => 'invoiceInsert',
      EntityHookEvents::ENTITY_UPDATE => 'invoiceUpdate',
      EntityHookEvents::ENTITY_DELETE => 'invoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function invoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    \Drupal::service('se_invoice.service')->preSave($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    \Drupal::service('se_invoice.service')->statusTotalUpdate($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    // Don't update invoice and customer balance when triggered by payment.
    if ($invoice->isSkipSaveEvents()) {
      return;
    }

    \Drupal::service('se_invoice.service')->statusTotalUpdate($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    \Drupal::service('se_invoice.service')->deleteUpdate($invoice);
  }

}
