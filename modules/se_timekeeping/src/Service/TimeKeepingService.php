<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Functions for working with timekeeping in invoices.
 *
 * @package Drupal\se_timekeeping\Service
 */
class TimeKeepingService implements TimeKeepingServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function reconcileTimekeeping(Invoice $invoice): void {

    $reconcileList = $this->loadOldTimekeeping($invoice);

    foreach ($invoice->se_item_lines as $itemLine) {
      if (($itemLine->target_type === 'se_timekeeping')
        && ($timekeeping = Timekeeping::load($itemLine->target_id))) {
        $reconcileList[$timekeeping->id()] = $this->markTimekeepingInvoiced($timekeeping, $invoice);
      }
    }

    // Look through all the timekeeping entries and save them now.
    foreach ($reconcileList as $timekeeping) {
      $timekeeping->save();
    }
  }

  /**
   * Load the old timekeeping entries and mark them as not invoiced.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that is being processed.
   *
   * @return array
   *   The list of items marked as available.
   */
  private function loadOldTimekeeping(Invoice $invoice): array {
    $reconcileList = [];

    /** @var \Drupal\se_invoice\Entity\Invoice|null $oldInvoice */
    if (!$oldInvoice = $invoice->getOldInvoice()) {
      return [];
    }

    foreach ($oldInvoice->se_item_lines as $itemLine) {
      if ($itemLine->target_type !== 'se_timekeeping') {
        continue;
      }

      /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
      $timekeeping = $reconcileList[$itemLine->target_id]
        ?? Timekeeping::load($itemLine->target_id);

      $reconcileList[$timekeeping->id()] = $this->markTimekeepingAvailable($timekeeping);
    }

    return $reconcileList;
  }

  /**
   * Mark timekeeping invoiced.
   *
   * @param \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping
   *   The Timekeeping entry to make as invoiced.
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to associate with the timekeeping.
   *
   * @return \Drupal\se_timekeeping\Entity\Timekeeping
   *   The Timekeeping entry.
   */
  private function markTimekeepingInvoiced(Timekeeping $timekeeping, Invoice $invoice): Timekeeping {
    $timekeeping
      ->set('se_billed', TRUE)
      ->set('se_in_ref', $invoice);
    return $timekeeping;
  }

  /**
   * Mark timekeeping available, called on invoice delete.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice that containers the timekeeping entries.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function markTimekeepingAvailableSave(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      if (($itemLine->target_type === 'se_timekeeping') && ($timekeeping = Timekeeping::load($itemLine->target_id))) {
        $this->markTimekeepingAvailable($timekeeping);
        $timekeeping->save();
      }
    }
  }

  /**
   * Make a timekeeping entry as available.
   *
   * @param \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping
   *   The timekeeping entry.
   *
   * @return \Drupal\se_timekeeping\Entity\Timekeeping
   *   The adjusted timekeeping entry.
   */
  private function markTimekeepingAvailable(Timekeeping $timekeeping): Timekeeping {
    $timekeeping
      ->set('se_billed', FALSE)
      ->set('se_in_ref', NULL);
    return $timekeeping;
  }

}
