<?php

namespace Drupal\se_stock\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;

/**
 * Various Stock Item related functions.
 *
 * @package Drupal\se_stock\Service
 */
class StockService implements StockServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function ensureItem(Item $item): void {
    if (!empty($item->se_serial->value)) {
      $query = \Drupal::entityQuery('se_item')
        ->accessCheck(TRUE)
        ->condition('type', 'se_stock')
        ->notExists('se_serial')
        ->condition('se_code', $item->se_code->value);
      $items = $query->execute();

      /** @var \Drupal\se_item\Entity\Item $stockItem */
      if (empty($items)) {
        $stockItem = Item::create([
          'type' => 'se_stock',
          'uid' => $item->uid->target_id,
          'name' => $item->name->value,
          'se_code' => ['value' => $item->se_code->value],
          'se_serial' => ['value' => ''],
          'se_sell_price' => ['value' => $item->se_sell_price->value],
          'se_cost_price' => ['value' => $item->se_cost_price->value],
        ]);
        if (isset($item->se_product_type_ref)) {
          $stockItem->se_product_type_ref->target_id = $item->se_product_type_ref->target_id;
        }
        if (isset($item->se_manufacturer_ref)) {
          $stockItem->se_manufacturer_ref->target_id = $item->se_manufacturer_ref->target_id;
        }
        if (isset($item->se_sale_category_ref)) {
          $stockItem->se_sale_category_ref->target_id = $item->se_sale_category_ref->target_id;
        }
        $stockItem->save();
      }
      else {
        $stockItem = Item::load(reset($items));
      }
    }

    if (isset($stockItem)) {
      $item->se_it_ref->target_id = $stockItem->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function reconcileItems(Invoice $invoice): void {
    $date = new DateTimePlus('now', date_default_timezone_get());

    $reconcileList = $this->markItemsAvailable($invoice);

    foreach ($invoice->se_item_lines as $itemLine) {
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

    /** @var \Drupal\se_invoice\Entity\Invoice|null $oldInvoice */
    if (!$oldInvoice = $invoice->getOldInvoice()) {
      return [];
    }

    foreach ($oldInvoice->se_item_lines as $itemLine) {
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
        ->set('se_sale_date', $date->format('Y-m-d'))
        ->set('se_sale_price', $price)
        ->set('se_sold', TRUE)
        ->set('se_in_ref', $invoice->id());
    }
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function markItemsAvailableSave(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      // Only operate on items.
      // Only operator on stock items.
      if (($itemLine->target_type === 'se_item') && ($item = Item::load($itemLine->target_id))
        && in_array($item->bundle(), ['se_stock', 'se_assembly'])) {
        $this->markItemAvailable($item);
        $item->save();
      }
    }
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
        ->set('se_sale_date', NULL)
        ->set('se_sale_price', NULL)
        ->set('se_sold', FALSE)
        ->set('se_in_ref', NULL);
    }
    return $item;
  }

}
