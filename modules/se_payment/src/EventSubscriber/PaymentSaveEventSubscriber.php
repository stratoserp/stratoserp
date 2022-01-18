<?php

declare(strict_types=1);

namespace Drupal\se_payment\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Drupal\stratoserp\Constants;
use Drupal\stratoserp\Traits\EventTrait;
use Drupal\se_payment\Traits\PaymentTrait;

/**
 * Class PaymentSaveEventSubscriber.
 *
 * For each invoice in the payment, mark it as paid.
 *
 * @see \Drupal\se_invoice\EventSubscriber\InvoiceSaveEventSubscriber
 *
 * @package Drupal\se_payment\EventSubscriber
 */
class PaymentSaveEventSubscriber implements PaymentSaveEventSubscriberInterface {

  use EventTrait;
  use PaymentTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'paymentInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'paymentUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentPresave',
      HookEventDispatcherInterface::ENTITY_DELETE => 'paymentDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function paymentInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $entity */
    $entity = $event->getEntity();

    if (!($entity instanceof Payment)) {
      return;
    }

    $amount = $this->updateInvoices($entity);
    $this->updateBusinessBalance($entity, $amount);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $entity */
    $entity = $event->getEntity();

    if (!($entity instanceof Payment)) {
      return;
    }

    $amount = $this->updateInvoices($entity);
    $this->updateBusinessBalance($entity, $amount);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentPresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $entity */
    $entity = $event->getEntity();

    if (!($entity instanceof Payment) || $entity->isNew()) {
      return;
    }

    $amount = $this->updateInvoices($entity, FALSE);
    $this->updateBusinessBalance($entity, $amount);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $entity */
    $entity = $event->getEntity();

    if (!($entity instanceof Payment)) {
      return;
    }

    $amount = $this->updateInvoices($entity, FALSE);
    $this->updateBusinessBalance($entity, $amount);
  }

  /**
   * Update invoices in the payment.
   *
   * Loop through the payment entries and mark the invoices as
   * paid/unpaid as dictated by the parameter.
   *
   * @param \Drupal\se_payment\Entity\Payment $payment
   *   The payment to work through.
   * @param bool $paid
   *   Whether the invoices should be marked paid, ot not.
   *
   * @return int
   *   The new payment amount.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateInvoices(Payment $payment, bool $paid = TRUE): int {
    $amount = 0;
    foreach ($payment->se_payment_lines as $paymentLine) {
      // Don't try on operate on invoices with no payment.
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      if (!empty($paymentLine->amount)
      && $invoice = Invoice::load($paymentLine->target_id)) {
        // Set a dynamic field on the entity so that other events dont try and
        // do things that we will take care of once the save is complete.
        // This avoids re-calculating the total outstanding for a customer, for
        // example.
        //
        // $event->stopPropagation() didn't appear to work for this.
        //
        // This event will save the invoice, avoid an invoice save in any other
        // triggered events.
        $this->setSkipInvoiceSaveEvents($invoice);

        // This event updates the total, avoid it in other triggered events.
        $business = \Drupal::service('se_business.service')->lookupBusiness($invoice);
        $this->setSkipBusinessXeroEvents($business);

        $invoice->set('se_status_ref', \Drupal::service('se_invoice.service')->checkInvoiceStatus($invoice, $paymentLine->amount));
        $invoice->set('se_outstanding', $invoice->getInvoiceBalance());
        $invoice->save();

        $amount += $paymentLine->amount;
      }
    }

    if ($paid) {
      $amount *= -1;
    }

    return (int) $amount;
  }

  /**
   * On payment, update the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The business entity to update the balance of.
   * @param int $amount
   *   The amount to set the balance to in cents.
   *
   * @return void|int
   *   New balance.
   */
  private function updateBusinessBalance(EntityInterface $entity, int $amount): ?int {
    if ($amount === 0) {
      return 0;
    }

    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($entity)) {
      \Drupal::logger('se_business_accounting_save')->error('No business set for %entity', ['%entity' => $entity->id()]);
      return 0;
    }

    return \Drupal::service('se_business.service')->adjustBalance($business, $amount);
  }

}
