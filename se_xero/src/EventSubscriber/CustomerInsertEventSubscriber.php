<?php

namespace Drupal\se_xero\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\Traits\ErpEventTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerInsertEventSubscriber.
 *
 * When a customer is added or updated, sync through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class CustomerInsertEventSubscriber implements EventSubscriberInterface {

  use ErpEventTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'customerInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'customerUpdate',
    ];
  }

  /**
   * When a customer is inserted, create the same customer in Xero.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event to work with.
   */
  public function customerInsert(EntityInsertEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($this->isSkipCustomerXeroEvents($entity)) {
      return;
    }

    if ($entity->bundle() === 'se_customer' && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.contact_service')->sync($entity);
    }
  }

  /**
   * When a customer is updated, update in Xero.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event to work with.
   */
  public function customerUpdate(EntityUpdateEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($this->isSkipCustomerXeroEvents($entity)) {
      return;
    }

    if ($entity->bundle() === 'se_customer' && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.contact_service')->sync($entity);
    }
  }

}
