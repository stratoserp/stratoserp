<?php

declare(strict_types=1);

namespace Drupal\se_xero\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_customer\Entity\Customer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerInsertEventSubscriber.
 *
 * When a customer is added or updated, sync through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class XeroCustomerEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'xeroCustomerInsert',
      EntityHookEvents::ENTITY_UPDATE => 'xeroCustomerUpdate',
    ];
  }

  /**
   * When a customer is inserted, create the same customer in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event to work with.
   */
  public function xeroCustomerInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_customer\Entity\Customer $customer */
    $customer = $event->getEntity();
    if (!$customer instanceof Customer) {
      return;
    }

    if ($customer->isSkipXeroEvents($customer)) {
      return;
    }

    \Drupal::service('se_xero.contact_service')->sync($customer);
  }

  /**
   * When a customer is updated, update in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event to work with.
   */
  public function xeroCustomerUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_customer\Entity\Customer $customer */
    $customer = $event->getEntity();
    if (!$customer instanceof Customer) {
      return;
    }

    if ($customer->isSkipXeroEvents($customer)) {
      return;
    }

    \Drupal::service('se_xero.contact_service')->sync($customer);
  }

}
