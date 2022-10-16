<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Service;

use Drupal\se_invoice\Entity\Invoice;

interface TimeKeepingServiceInterface {

  /**
   * Loop through the invoice entries and mark the entries as billed.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkItemsBilled(Invoice $invoice): void;

  /**
   * Loop through the invoice entries and mark the entries as not billed.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkItemsUnBilled(Invoice $invoice): void;

}
