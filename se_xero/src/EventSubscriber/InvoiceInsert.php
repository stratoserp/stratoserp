<?php

namespace Drupal\se_xero\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceInsert implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
    ];
  }

  /**
   * @param EntityInsertEvent $event
   *
   */
  public function invoiceInsert(EntityInsertEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getEntity();
    // TODO Check for the xero uuid instead?
    if ($node->bundle() === 'se_customer' && !$node->get('se_xero_uuid')) {
      $event->node = $this->xeroSync($node);
    }
  }

  public function invoiceUpdate(EntityUpdateEvent $event) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $event->getEntity();
    if ($node->bundle() === 'se_customer' && !$node->get('se_xero_uuid')) {
      $event->node = $this->xeroSync($node);
    }

  }

  private function xeroSync($node) {
    if ($result = \Drupal::service('se_xero.invoice_service')->sync($node)) {
      return $node;
    }
  }
}
