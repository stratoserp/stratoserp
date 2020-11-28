<?php

declare(strict_types=1);

namespace Drupal\se_payment\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\stratoserp\Traits\ErpEventTrait;
use Drupal\se_payment\Traits\ErpPaymentTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\Entity\Node;

/**
 * Class PaymentSaveEventSubscriber.
 *
 * For each invoice in the payment, mark it as paid.
 *
 * @see \Drupal\se_invoice\EventSubscriber\InvoiceSaveEventSubscriber
 *
 * @package Drupal\se_payment\EventSubscriber
 */
class PaymentEventSubscriber implements EventSubscriberInterface {

  use ErpEventTrait;
  use ErpPaymentTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'paymentInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'paymentUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentPresave',
    ];
  }

  /**
   * When a payment is saved, mark all invoices listed as paid.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_payment') {
      return;
    }

    $amount = $this->updateInvoices($entity);
    $this->updateCustomerBalance($entity, $amount);
  }

  /**
   * Whn a payment is updated, make all invoices as paid.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function paymentUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_payment') {
      return;
    }

    $amount = $this->updateInvoices($entity);
    $this->updateCustomerBalance($entity, $amount);
  }

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
  public function paymentPresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->isNew()
      || $entity->bundle() !== 'se_payment') {
      return;
    }

    $amount = $this->updateInvoices($entity, FALSE);
    $this->updateCustomerBalance($entity, $amount);
  }

  /**
   * Update invoices in the payment.
   *
   * Loop through the payment entries and mark the invoices as
   * paid/unpaid as dictated by the parameter.
   *
   * @param \Drupal\node\Entity\Node $entity
   *   The payment node to work through.
   * @param bool $paid
   *   Whether the invoices should be marked paid, ot not.
   *
   * @return int
   *   The new payment amount.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateInvoices(Node $entity, bool $paid = TRUE): int {
    // TODO: Configurable?
    if ($paid) {
      $term = \Drupal::service('se_invoice.service')->getPaidTerm();
    }
    else {
      $term = \Drupal::service('se_invoice.service')->getOpenTerm();
    }

    $bundle_field_type = 'se_' . ErpCore::PAYMENT_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    $amount = 0;
    foreach ($entity->{$bundle_field_type . '_lines'} as $payment_line) {
      // Don't try on operate on invoices with no payment.
      /** @var \Drupal\node\Entity\Node $invoice */
      if (!empty($payment_line->amount)
      && $invoice = Node::load($payment_line->target_id)) {
        // Set a dynamic field on the node so that other events dont try and
        // do things that we will take care of once save things multiple times
        // for no reason.
        // $event->stopPropagation() didn't appear to work for this.
        //
        // This event saves the invoice.
        $this->setSkipInvoiceSaveEvents($invoice);

        // This event updates the total at the end.
        $this->setSkipCustomerXeroEvents($invoice);

        // TODO: Make a service for this?
        if ($payment_line->amount === $invoice->se_in_total->value
        || $payment_line->amount === $invoice->se_in_outstanding->value) {
          $invoice->set('se_status_ref', $term);
        }
        else {
          // Update the outstanding amount if required.
          $invoice->se_in_outstanding->value =
            $this->getInvoiceBalance($invoice);
        }

        $invoice->save();
        $amount += $payment_line->amount;
      }
    }

    if ($paid) {
      $amount *= -1;
    }

    return (int) $amount;
  }

  /**
   * On payment, update the customer balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The customer entity to update the balance of.
   * @param int $amount
   *   The amount to set the balance to in cents.
   *
   * @return void|int
   *   New balance.
   */
  private function updateCustomerBalance(EntityInterface $entity, int $amount): int {
    if ($amount === 0) {
      return 0;
    }

    if (!$customer = \Drupal::service('se_customer.service')->lookupCustomer($entity)) {
      \Drupal::logger('se_customer_accounting_save')->error('No customer set for %node', ['%node' => $entity->id()]);
      return 0;
    }

    return \Drupal::service('se_customer.service')->adjustBalance($customer, $amount);
  }

}
