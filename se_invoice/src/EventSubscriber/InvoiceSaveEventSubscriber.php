<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
class InvoiceSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'invoiceAdjust',
    ];
  }

  /**
   * Add the total of this invoice to the amount the customer owes.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   */
  public function invoiceInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if (isset($entity->skipInvoiceSaveEvents)) {
      return;
    }

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_invoice') {
      return;
    }
    $this->updateCustomerBalance($entity);
  }

  /**
   * Add the total of this invoice to the amount the customer owes.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    if (isset($entity->skipInvoiceSaveEvents)) {
      return;
    }

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_invoice') {
      return;
    }
    $this->updateCustomerBalance($entity);
  }

  /**
   * In case the amount changes on the saving of this entity, we need
   * to reduce the current balance by the old balance first.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   */
  public function invoiceAdjust(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if (isset($entity->skipInvoiceSaveEvents)) {
      return;
    }

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->isNew()
      || $entity->bundle() !== 'se_invoice') {
      return;
    }

    // Is this the right way?
    $this->updateCustomerBalance($entity, TRUE);
  }

  /**
   * On invoicing, update the customer balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param bool $reduce_balance
   *
   * @return void|int new balance.
   */
  private function updateCustomerBalance(EntityInterface $entity, $reduce_balance = FALSE): int {
    if (!$customer = \Drupal::service('se_customer.service')->lookupCustomer($entity)) {
      \Drupal::logger('se_customer_invoice_save')->error('No customer set for %node', ['%node' => $entity->id()]);
      return 0;
    }

    $bundle_field_type = 'field_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
    $amount = $entity->{$bundle_field_type . '_total'}->value;
    if ($reduce_balance) {
      $amount *= -1;
    }

    return \Drupal::service('se_customer.service')->adjustBalance($customer, (int)$amount);
  }

}
