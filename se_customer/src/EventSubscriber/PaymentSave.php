<?php

namespace Drupal\se_customer\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'paymentSave',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'paymentUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentReduce'
    ];
  }

  public function paymentSave(EntityInsertEvent $event) {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_payment') {
      return;
    }
    $this->updateCustomerBalance($entity);
  }

  public function paymentUpdate(EntityUpdateEvent $event) {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_payment') {
      return;
    }
    $this->updateCustomerBalance($entity);
  }

  public function paymentReduce(EntityPresaveEvent $event) {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_payment'
      || $entity->isNew()) {
      return;
    }

    // Is this the right way?
    $this->updateCustomerBalance($entity, FALSE);
  }

  // On payment
  private function updateCustomerBalance(EntityInterface $entity, $reduce = TRUE) {
    if (!$customer = \Drupal::service('se_customer.service')->lookupCustomer($entity)) {
      \Drupal::logger('se_customer_payment_save')->error('No customer set for %node', ['%node' => $entity->id()]);
      return;
    }

    $amount = $entity->{'field_' . ErpCore::PAYMENTS_BUNDLE_MAP[$entity->bundle()] . '_total'}->value;
    if ($reduce) {
      $amount *= -1;
    }

    $balance = \Drupal::service('se_customer.service')->adjustBalance($customer, $amount);
  }

}
