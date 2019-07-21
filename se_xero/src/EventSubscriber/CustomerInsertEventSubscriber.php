<?php

namespace Drupal\se_xero\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerInsertEventSubscriber.
 *
 * When a customer is added or updated, sync through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class CustomerInsertEventSubscriber implements EventSubscriberInterface {

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
   */
  public function customerInsert(EntityInsertEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getEntity();
    if ($node->bundle() === 'se_customer' && !$node->get('se_xero_uuid')) {
      \Drupal::service('se_xero.contact_service')->sync($node);
    }
  }

  /**
   *
   */
  public function customerUpdate(EntityUpdateEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getEntity();
    if ($node->bundle() === 'se_customer' && !$node->get('se_xero_uuid')) {
      \Drupal::service('se_xero.contact_service')->sync($node);
    }
  }

}
