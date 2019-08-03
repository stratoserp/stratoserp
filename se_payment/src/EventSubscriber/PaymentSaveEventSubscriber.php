<?php

namespace Drupal\se_payment\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\taxonomy\Entity\Term;
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
class PaymentSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'paymentInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'paymentUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentAdjust',
    ];
  }

  /**
   * When a payment is saved, mark all invoices listed as paid.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   */
  public function paymentInsert(EntityInsertEvent $event) {
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
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   */
  public function paymentUpdate(EntityUpdateEvent $event) {
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
   * When a payment is about to be saved, mark any existing payment lines
   * in its pre-saved state as unpaid in case they have been removed from the
   * payment.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   */
  public function paymentAdjust(EntityPresaveEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->isNew()
      || $entity->bundle() !== 'se_payment') {
      return;
    }

    $amount = $this->updateInvoices($entity, FALSE);
    $this->updateCustomerBalance($entity, $amount, TRUE);
  }

  /**
   * Loop through the payment entries and mark the invoices as
   * paid/unpaid as dictated by the parameter.
   *
   * @param \Drupal\node\Entity\Node $entity
   * @param bool $paid
   *
   * @return int
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateInvoices($entity, $paid = TRUE) {
    // TODO - Configurable?
    if ($paid) {
      $term = \Drupal::service('se_invoice.service')->getPaidTerm();
    }
    else {
      $term = \Drupal::service('se_invoice.service')->getOpenTerm();
    }

    $bundle_field_type = 'field_' . ErpCore::PAYMENT_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    $amount = 0;
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      if ($invoice = Node::load($item_line->target_id)) {
        // TODO - Make a service for this?
        $invoice->set('field_status_ref', $term);

        // Set a dynamic field on the node so that other events dont try and
        // do things that we will take care of once save things multiple times for no reason.
        // $event->stopPropagation() didn't appear to work for this.
        $invoice->skipInvoiceSaveEvents = TRUE;  // This event saves the invoice.
        $invoice->skipCustomerXeroEvents = TRUE; // This event updates the total at the end.

        $invoice->save();
        $amount += $item_line->amount;
      }
    }

    if ($paid) {
      $amount *= -1;
    }

    return $amount;
  }


  /**
   * On payment, update the customer balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $amount
   * @param bool $increase_balance
   *
   * @return void|int new balance.
   */
  private function updateCustomerBalance(EntityInterface $entity, $amount, $increase_balance = TRUE) {
    if ($amount === 0) {
      return;
    }

    if (!$customer = \Drupal::service('se_customer.service')->lookupCustomer($entity)) {
      \Drupal::logger('se_customer_accounting_save')->error('No customer set for %node', ['%node' => $entity->id()]);
      return;
    }

    return \Drupal::service('se_customer.service')->adjustBalance($customer, $amount);
  }

}
