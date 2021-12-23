<?php

declare(strict_types=1);

namespace Drupal\se_item\EventSubscriber;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * Update item status if they have been invoiced out.
 *
 * @package Drupal\se_item\EventSubscriber
 */
class ItemInvoiceEventSubscriber implements ItemInvoiceEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemInvoicePresave',
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemInvoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'itemInvoiceUpdate',
      HookEventDispatcherInterface::ENTITY_DELETE => 'itemInvoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if (!($entity instanceof Invoice)) {
      return;
    }

    // Store the existing items in the object for later reconciliation.
    $entity->se_in_lines_old = $entity->se_in_lines;
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if (!($entity instanceof Invoice)) {
      return;
    }

    $this->reconcileItems($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if (!($entity instanceof Invoice)) {
      return;
    }

    $this->reconcileItems($entity);
  }

  /**
   * Mark the items as sold/in stock as dictated by the parameter.
   *
   * Work through the list from the pre-save, if there is one first, then
   * replace the items in that list with the ones in this invoice, then
   * finally save them all. This will mean only a single save is required
   * for each item, much better that the previous way.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function reconcileItems(Invoice $invoice): void {
    $reconcileList = [];

    // @todo should this be retrieved from the invoice?
    $date = new DateTimePlus(NULL, date_default_timezone_get());

    if (isset($invoice->se_in_lines_old)) {
      $reconcileList = $this->markItemsAvailable($invoice);
    }

    foreach ($invoice->{'se_in_lines'} as $itemLine) {
      // Only operate on items that are also stock items.
      if (($itemLine->target_type === 'se_item')
        && ($item = Item::load($itemLine->target_id))
        && $item->isStock()) {
        $reconcileList[$item->id()] = $this->markItemSold($item, $invoice, (int) $itemLine->price, $date);
      }
    }

    // Loop through the items and save them all now.
    foreach ($reconcileList as $item) {
      $item->save();
    }
  }

  /**
   * Change the items to be available, but don't save here.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @return array
   *   The list of items marked as available.
   */
  private function markItemsAvailable(Invoice $invoice): array {
    $reconcileList = [];

    foreach ($invoice->{'se_in_lines_old'} as $itemLine) {
      // Only operate on items that are also stock items.
      if (($itemLine->target_type === 'se_item')
        && ($item = Item::load($itemLine->target_id))
        && $item->isStock()) {
        $reconcileList[$item->id()] = $this->markItemAvailable($item);
      }
    }

    return $reconcileList;
  }

  /**
   * Mark an item as sold.
   *
   * @param \Drupal\se_item\Entity\Item $item
   *   The item to operate on.
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that the item is sold on.
   * @param int $price
   *   The price the item is being sold at.
   * @param \Drupal\Component\Datetime\DateTimePlus $date
   *   The date object for the date of sale.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The adjusted item.
   */
  private function markItemSold(Item $item, Invoice $invoice, int $price, DateTimePlus $date): Item {
    // Only operate on things that have a 'parent', not the parents
    // themselves, they are never sold.
    if ($item->hasParent()) {
      $item
        ->set('se_it_sale_date', $date->format('Y-m-d'))
        ->set('se_it_sale_price', $price)
        ->set('se_it_invoice_ref', $invoice->id());
    }
    return $item;
  }

  /**
   * Mark an item as available.
   *
   * @param \Drupal\se_item\Entity\Item $item
   *   The item to operate on.
   *
   * @return \Drupal\se_item\Entity\Item
   *   The adjusted item.
   */
  private function markItemAvailable(Item $item): Item {
    // Only operate on things that have a 'parent', not the parents
    // themselves, they are never sold.
    if ($item->hasParent()) {
      $item
        ->set('se_it_sale_date', NULL)
        ->set('se_it_sale_price', NULL)
        ->set('se_it_invoice_ref', NULL);
    }
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if (!($entity instanceof Invoice)) {
      return;
    }

    $this->markItemsAvailableSave($entity);
  }

  /**
   * When deleting, just go ahead and save the items.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function markItemsAvailableSave(Invoice $invoice): void {
    foreach ($invoice->{'se_in_lines'} as $itemLine) {
      // Only operate on items.
      // Only operator on stock items.
      if (($itemLine->target_type === 'se_item') && ($item = Item::load($itemLine->target_id))
        && in_array($item->bundle(), ['se_stock', 'se_assembly'])) {
        $this->markItemAvailable($item);
        $item->save();
      }
    }
  }

}
