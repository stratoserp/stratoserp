<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;

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
    if (!$invoice instanceof Invoice || $invoice->isNew()) {
      return;
    }

    // This is set by payments or they would be re-saving save the invoice.
    if ($invoice->getSkipSaveEvents()) {
      return;
    }

    \Drupal::service('se_invoice.service')->storeBalance($invoice);
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

    \Drupal::service('se_invoice.service')->statusTotalInsert($invoice);
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

    // This is set by payments or they would be re-saving save the invoice.
    if ($invoice->getSkipSaveEvents()) {
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

    // This is set by payments or they would be re-saving save the invoice.
    if ($invoice->getSkipSaveEvents()) {
      return;
    }

    \Drupal::service('se_invoice.service')->deleteUpdate($invoice);
  }

}
