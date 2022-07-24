<?php

declare(strict_types=1);

namespace Drupal\se_stock\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;

/**
 * Interface for stock item service.
 */
interface StockServiceInterface {

  /**
   * Update stock item.
   *
   * When a stock item is saved, if it has a serial number and no existing
   * stock item exists with no serial number, create one with no serial as
   * a 'parent' item to be used in quotes etc.
   *
   * @param \Drupal\se_item\Entity\Item $item
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function ensureItem(Item $item): Item;

  /**
   * Mark the items as sold.
   *
   * Work through the list from the pre-save and set the items to available.
   * Then update the items in that list with the ones now on the invoice.
   * Finally, save them all.
   *
   * This means only a single save is required for each item.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function reconcileItems(Invoice $invoice): void;

  /**
   * When deleting, just go ahead and save the items.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function markItemsAvailableSave(Invoice $invoice): void;

}
