<?php

namespace Drupal\se_bulk_stock\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;

/**
 * Various Bulk stock Item related functions.
 *
 * @package Drupal\se_bulk_stock\Service
 */
class BulkStockService implements BulkStockServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function reconcileItems(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      // Only operate on items that are also stock items.
      if (($itemLine->target_type === 'se_item')
        && ($item = Item::load($itemLine->target_id))
        && $item->isBulkStock()) {
        $reconcileList[$item->id()] = $item;
        $item->se_available->value -= $itemLine->quantity;
      }
    }

    // Loop through the items and save them all now.
    foreach ($reconcileList as $item) {
      $item->save();
    }
  }

}
