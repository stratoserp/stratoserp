<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\taxonomy\Entity\Term;

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
   * @return \Drupal\taxonomy\Entity\Term
   *   The invoice status
   */
  public function checkInvoiceStatus(Invoice $invoice, int $payment = NULL): Term;

  /**
   * Retrieve the term user for open status.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   The term for open status.
   */
  public function getOpenTerm(): ?Term;

  /**
   * Retrieve the term user for paid status.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   The term for paid status.
   */
  public function getClosedTerm(): ?Term;

}
