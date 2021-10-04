<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\stratoserp\Traits\ErpEventTrait;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * When an invoice is saved, adjust the business
 * balance by the amount of the invoice.
 *
 * @see \Drupal\se_payment\EventSubscriber\PaymentSaveEventSubscriber
 *
 * @package Drupal\se_invoice\EventSubscriber
 */
class InvoiceSaveEventSubscriber implements InvoiceSaveEventSubscriberInterface {

  use ErpEventTrait;
  use PaymentTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'invoicePresave',
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
      HookEventDispatcherInterface::ENTITY_DELETE => 'invoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function invoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if ($invoice->getEntityTypeId() !== 'se_invoice' || $invoice->isNew()) {
      return;
    }

    // This is set by payments as they re-save the invoice.
    if ($this->isSkipInvoiceSaveEvents($invoice)) {
      return;
    }

    // Store the values on the object before saving for adjustments afterwards.
    $invoice->se_in_old_total = $invoice->getTotal();
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if ($invoice->getEntityTypeId() !== 'se_invoice') {
      return;
    }

    // This is set by payments as they re-save the invoice.
    if ($this->isSkipInvoiceSaveEvents($invoice)) {
      return;
    }

    $invoice->set('se_status_ref', \Drupal::service('se_invoice.service')->checkInvoiceStatus($invoice));

    // On insert, the total is outstanding.
    $invoiceBalance = $invoice->getTotal();
    $invoice->set('se_in_outstanding', $invoiceBalance);

    $business = $invoice->getBusiness();
    $business->adjustBalance($invoiceBalance);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if ($invoice->getEntityTypeId() !== 'se_invoice') {
      return;
    }

    // This is set by payments as they re-save the invoice.
    if ($this->isSkipInvoiceSaveEvents($invoice)) {
      return;
    }

    $invoice->set('se_status_ref', \Drupal::service('se_invoice.service')->checkInvoiceStatus($invoice));

    $invoiceBalance = $invoice->getInvoiceBalance();
    $invoice->set('se_in_outstanding', $invoiceBalance);

    $business = $invoice->getBusiness();
    $business->adjustBalance($invoiceBalance - (int) $invoice->se_in_old_total);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if ($invoice->getEntityTypeId() !== 'se_invoice' || $invoice->isNew()) {
      return;
    }

    // This is set by payments as they re-save the invoice.
    if ($this->isSkipInvoiceSaveEvents($invoice)) {
      return;
    }

    $business = $invoice->getBusiness();
    $business->adjustBalance($invoice->getTotal() * -1);
  }

}
