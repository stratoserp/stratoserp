<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;
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

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'invoiceAdjust',
      HookEventDispatcherInterface::ENTITY_DELETE => 'invoiceDelete',
    ];
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

    $invoice = \Drupal::service('se_invoice.service')->checkCloseInvoice($invoice);

    $this->increaseBusinessBalance($invoice);
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

    $invoice = \Drupal::service('se_invoice.service')->checkCloseInvoice($invoice);

    $this->increaseBusinessBalance($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceAdjust(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if ($invoice->getEntityTypeId() !== 'se_invoice' || $invoice->isNew()) {
      return;
    }

    // This is set by payments as they re-save the invoice.
    if ($this->isSkipInvoiceSaveEvents($invoice)) {
      return;
    }

    $invoice = \Drupal::service('se_invoice.service')->checkCloseInvoice($invoice);

    $this->reduceBusinessBalance($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if ($invoice->getEntityTypeId() !== 'se_invoice'
      || $invoice->isNew()) {
      return;
    }

    // This is set by payments as they re-save the invoice.
    if ($this->isSkipInvoiceSaveEvents($invoice)) {
      return;
    }

    $this->reduceBusinessBalance($invoice);
  }

  /**
   * On invoicing, increase the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $invoice
   *   The event we are working with.
   *
   * @return void|int
   *   The new balance is returned.
   */
  private function increaseBusinessBalance(EntityInterface $invoice): int {
    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($invoice)) {
      \Drupal::logger('se_business_invoice_save')->error('No business set for %entity', ['%entity' => $invoice->id()]);
      return 0;
    }

    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];
    $amount = $invoice->{$bundleFieldType . '_total'}->value;

    return \Drupal::service('se_business.service')->adjustBalance($business, (int) $amount);
  }

  /**
   * On invoicing, reduce the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $invoice
   *   The event we are working with.
   *
   * @return void|int
   *   The new balance is returned.
   */
  private function reduceBusinessBalance(EntityInterface $invoice): int {
    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($invoice)) {
      \Drupal::logger('se_business_invoice_save')->error('No business set for %entity', ['%entity' => $invoice->id()]);
      return 0;
    }

    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];
    $amount = $invoice->{$bundleFieldType . '_total'}->value;
    $amount *= -1;

    return \Drupal::service('se_business.service')->adjustBalance($business, (int) $amount);
  }

}
