<?php

declare(strict_types=1);

namespace Drupal\se_xero\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\stratoserp\Traits\EventTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceInsertEventSubscriber.
 *
 * When an invoice is saved, sync it through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class XeroInvoiceEventSubscriber implements EventSubscriberInterface {

  use EventTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'xeroInvoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'xeroInvoiceUpdate',
    ];
  }

  /**
   * Add a new invoice to Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function xeroInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    // @todo Check for the xero uuid instead?
    if ($entity instanceof Invoice && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.invoice_service')->sync($entity);
    }
  }

  /**
   * Update an existing invoice in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   */
  public function xeroInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    if ($entity instanceof Invoice && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.invoice_service')->sync($entity);
    }

  }

}
