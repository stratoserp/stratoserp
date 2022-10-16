<?php

declare(strict_types=1);

namespace Drupal\se_stock\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;
use Drupal\se_stock\Service\StockServiceInterface;

/**
 * Class StockItemPresaveEventSubscriber.
 *
 * Create 'parent' items for stock items if they don't already exist.
 *
 * @package Drupal\se_stock\EventSubscriber
 */
class StockItemEventSubscriber implements StockItemEventSubscriberInterface {

  /** @var \Drupal\se_stock\Service\StockServiceInterface */
  protected StockServiceInterface $stockService;

  public function __construct(StockServiceInterface $stockService) {
    $this->stockService = $stockService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'stockItemPresave',
      EntityHookEvents::ENTITY_INSERT => 'stockItemInvoiceInsert',
      EntityHookEvents::ENTITY_UPDATE => 'stockItemInvoiceUpdate',
      EntityHookEvents::ENTITY_DELETE => 'stockItemInvoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function stockItemPresave(EntityPresaveEvent $event): void {
    $item = $event->getEntity();
    if (!$item instanceof Item) {
      return;
    }

    if ($item->bundle() !== 'se_stock') {
      return;
    }

    $item = $this->stockService->ensureItem($item);
  }

  /**
   * {@inheritdoc}
   */
  public function stockItemInvoiceInsert(EntityInsertEvent $event): void {
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
  public function stockItemInvoiceUpdate(EntityUpdateEvent $event): void {
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
  public function stockItemInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!($invoice instanceof Invoice)) {
      return;
    }

    $this->stockService->markItemsAvailableSave($invoice);
  }

}
