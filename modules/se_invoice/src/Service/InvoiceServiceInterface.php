<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\se_invoice\Entity\Invoice;

/**
 * Interface for the invoice service.
 */
interface InvoiceServiceInterface {

  /**
   * Check if an invoice should be marked as paid.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The Invoice entity.
   * @param int|null $payment
   *   The paid amount.
   *
   * @return string
   *   The invoice status.
   */
  public function checkInvoiceStatus(Invoice $invoice, int $payment = NULL): string;

}
