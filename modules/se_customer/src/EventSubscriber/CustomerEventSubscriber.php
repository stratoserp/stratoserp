<?php

declare(strict_types=1);

namespace Drupal\se_customer\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Class CustomerEventSubscriber.
 *
 * Trigger various customer related things based on other entity
 * insert/update.
 *
 * @package Drupal\se_customer\EventSubscriber
 */
class CustomerEventSubscriber implements CustomerEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => ['entityInsert', -100],
      EntityHookEvents::ENTITY_UPDATE => ['entityUpdate', -100],
      EntityHookEvents::ENTITY_DELETE => ['entityDelete', -100],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof Invoice || $entity instanceof Payment) {
      $this->mayChangeCustomerBalance($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof Invoice || $entity instanceof Payment) {
      $this->mayChangeCustomerBalance($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityDelete(EntityDeleteEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof Invoice || $entity instanceof Payment) {
      $this->mayChangeCustomerBalance($entity);
    }
  }

  /**
   * If the entity type might affect the customer balance, update the balance.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   The entity that may affect this customers balance.
   */
  private function mayChangeCustomerBalance(StratosEntityBaseInterface $entity) {
    // On invoice, we may not have a customer.
    if ($customer = $entity->getCustomer()) {
      $customer->updateBalance();
    }
  }

}
