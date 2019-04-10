<?php

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceSave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceSave',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'invoiceReduce'
    ];
  }

  public function invoiceSave(EntityInsertEvent $event) {
    $entity = $event->getEntity();
    $this->updateCustomerBalance($entity);
  }

  public function invoiceUpdate(EntityUpdateEvent $event) {
    $entity = $event->getEntity();
    $this->updateCustomerBalance($entity);
  }

  public function invoiceReduce(EntityUpdateEvent $event) {
    $entity = $event->getEntity();
    // TODO reduce balance here in case total of invoice is changed.
    $this->updateCustomerBalance($entity);
  }

  // On invoice
  private function updateCustomerBalance($entity) {
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $customer = \Drupal::service('se_customer.service')->lookupCustomer($entity);

      $balance = \Drupal::service('se_customer.service')->adjustBalance($customer, $entity->total);
    }
  }

}
