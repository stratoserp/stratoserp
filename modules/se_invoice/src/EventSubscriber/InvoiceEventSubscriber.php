<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
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
 * @see \Drupal\se_payment\EventSubscriber\PaymentEventSubscriber
 *
 * @package Drupal\se_invoice\EventSubscriber
 */
class InvoiceEventSubscriber implements InvoiceEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => ['invoicePreAction', 25],
      EntityHookEvents::ENTITY_INSERT => ['invoiceInsert', -25],
      EntityHookEvents::ENTITY_UPDATE => ['invoiceUpdate', -25],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function invoicePreAction(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    $invoice->storeOldInvoice();

    // If the total changed, then the outstanding amount will need to as well.
    $oldInvoice = $invoice->getOldInvoice();
    if ($oldInvoice && $difference = $invoice->getTotal() - $oldInvoice->getTotal()) {
      $invoice->setOutstanding($invoice->getOutstanding() + $difference);
    }
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

    $customer = $invoice->getCustomer();
    $customer->setBalance($customer->updateBalance());
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

    $customer = $invoice->getCustomer();
    $customer->setBalance($customer->updateBalance());
  }

}
