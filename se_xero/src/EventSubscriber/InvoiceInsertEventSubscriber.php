<?php

namespace Drupal\se_xero\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceInsertEventSubscriber.
 *
 * When an invoice is saved, sync it through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class InvoiceInsertEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /* @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
    ];
  }

  /**
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   */
  public function invoiceInsert(EntityInsertEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getEntity();
    // TODO Check for the xero uuid instead?
    if ($node->bundle() === 'se_customer' && !$node->get('se_xero_uuid')) {
      \Drupal::service('se_xero.invoice_service')->sync($node);
    }
  }

  /**
   * Sync an invoice up to xero.
   */
  public function invoiceUpdate(EntityUpdateEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getEntity();
    if ($node->bundle() === 'se_customer' && !$node->get('se_xero_uuid')) {
      \Drupal::service('se_xero.invoice_service')->sync($node);
    }

  }

}
