<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Functions for working with timekeeping in invoices.
 *
 * @todo convert these to the reconciliation style, see item, payment.
 *
 * @package Drupal\se_timekeeping\Service
 */
class TimeKeepingService {

  /**
   * Loop through the invoice entries and mark the entries as billed.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkItemsBilled(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      if ($itemLine->target_type === 'se_timekeeping') {
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if ($timekeeping = Timekeeping::load($itemLine->target_id)) {
          $timekeeping->se_billed->value = TRUE;
          $timekeeping->se_in_ref = $invoice;
          $timekeeping->save();
        }
      }
    }
  }

  /**
   * Loop through the invoice entries and mark the entries as not billed.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkItemsUnBilled(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      if ($itemLine->target_type === 'se_timekeeping') {
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if ($timekeeping = Timekeeping::load($itemLine->target_id)) {
          $timekeeping->se_billed->value = FALSE;
          $timekeeping->se_in_ref = NULL;
          $timekeeping->save();
        }
      }
    }
  }

}
