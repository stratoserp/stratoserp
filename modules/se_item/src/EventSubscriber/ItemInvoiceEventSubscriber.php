<?php

declare(strict_types=1);

namespace Drupal\se_item\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_stock\Service\StockServiceInterface;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * @todo Move these to the StockItemEventSubscriber, where they should be?
 * Update item status if they have been invoiced out.
 *
 * @package Drupal\se_item\EventSubscriber
 */
class ItemInvoiceEventSubscriber implements ItemInvoiceEventSubscriberInterface {

  protected StockServiceInterface $stockService;

  public function __construct(StockServiceInterface $stockService) {
    $this->stockService = $stockService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'itemInvoiceInsert',
      EntityHookEvents::ENTITY_UPDATE => 'itemInvoiceUpdate',
      EntityHookEvents::ENTITY_DELETE => 'itemInvoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!($invoice instanceof Invoice)) {
      return;
    }

    // Don't update items when called from a payment save.
    if ($invoice->isSkipSaveEvents()) {
      return;
    }

    $this->stockService->reconcileItems($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!($invoice instanceof Invoice)) {
      return;
    }

    // Don't update items when called from a payment save.
    if ($invoice->isSkipSaveEvents()) {
      return;
    }

    $this->stockService->reconcileItems($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!($invoice instanceof Invoice)) {
      return;
    }

    $this->stockService->markItemsAvailableSave($invoice);
  }

}
