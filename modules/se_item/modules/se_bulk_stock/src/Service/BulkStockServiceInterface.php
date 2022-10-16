<?php

declare(strict_types=1);

namespace Drupal\se_bulk_stock\Service;

use Drupal\se_invoice\Entity\Invoice;

/**
 * Various Bulk stock Item related functions.
 *
 * @package Drupal\se_bulk_stock\Service
 */
interface BulkStockServiceInterface {

  /**
   * Decrease the number of bulk items available.
   *
   * This means only a single save is required for each item.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function reconcileItems(Invoice $invoice): void;

}
