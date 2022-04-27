<?php

namespace Drupal\se_untracked_stock\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;

/**
 * Various Stock Item related functions.
 *
 * @package Drupal\se_untracked_stock\Service
 */
class UntrackedStockService {

  /**
   * Decrease the number of untracked items available.
   *
   * This means only a single save is required for each item.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function reconcileItems(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      // Only operate on items that are also stock items.
      if (($itemLine->target_type === 'se_item')
        && ($item = Item::load($itemLine->target_id))
        && $item->isUntrackedStock()) {
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
