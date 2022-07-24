<?php

declare(strict_types=1);

namespace Drupal\se_xero\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_invoice\Entity\Invoice;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceInsertEventSubscriber.
 *
 * When an invoice is saved, sync it through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class XeroInvoiceEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'xeroInvoiceInsert',
      EntityHookEvents::ENTITY_UPDATE => 'xeroInvoiceUpdate',
    ];
  }

  /**
   * Add a new invoice to Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function xeroInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    if ($invoice->isSkipSaveEvents()) {
      return;
    }

    \Drupal::service('se_xero.invoice_service')->sync($invoice);
  }

  /**
   * Update an existing invoice in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   */
  public function xeroInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    if ($invoice->isSkipSaveEvents()) {
      return;
    }

    \Drupal::service('se_xero.invoice_service')->sync($invoice);
  }

}
