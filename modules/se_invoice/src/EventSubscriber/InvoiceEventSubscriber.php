<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_invoice\Service\InvoiceServiceInterface;

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

  protected InvoiceServiceInterface $invoiceService;

  /**
   * Simple constructor.
   */
  public function __construct(InvoiceServiceInterface $invoiceService) {
    $this->invoiceService = $invoiceService;
  }

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

    if ($invoice->isNew()) {
      $invoice->setOutstanding($invoice->getTotal());
    }
    else {
      $invoice->setOutstanding($invoice->getInvoiceBalance());
    }

    // Either way, set the status.
    $invoice->se_status = $this->invoiceService->checkInvoiceStatus($invoice);
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
    $invoice->setOutstanding($invoice->getInvoiceBalance());
  }

}
