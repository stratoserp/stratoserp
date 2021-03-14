<?php

declare(strict_types=1);

namespace Drupal\se_payment\Traits;

use Drupal\Core\Database\Database;
use Drupal\se_invoice\Entity\Invoice;

/**
 * Trait to provide drupal payment utilities.
 */
trait PaymentTrait {

  /**
   * Retrieve payments for passed invoice.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to retrieve information for.
   *
   * @return mixed
   *   Return an array of payment entities.
   */
  public function getInvoicePayments(Invoice $invoice): array {
    // @todo Is there a nicer way to do this?
    $query = Database::getConnection()->select('se_payment__se_pa_lines', 'nfpl');
    $query->fields('nfpl', ['entity_id']);
    $query->condition('nfpl.se_pa_lines_target_id', $invoice->id());
    $result = $query->execute();
    if ($result) {
      return $result->fetchAll();
    }
    return [];
  }

  /**
   * Retrieve payment amounts.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to retrieve information for.
   *
   * @return mixed
   *   Return an array of payment amounts.
   */
  private function getInvoicePaymentAmounts(Invoice $invoice): array {
    // @todo Is there a nicer way to do this?
    $query = Database::getConnection()->select('se_payment__se_pa_lines', 'nfpl');
    $query->fields('nfpl', ['se_pa_lines_amount']);
    $query->condition('nfpl.se_pa_lines_target_id', $invoice->id());
    $result = $query->execute();
    if ($result) {
      return $result->fetchAll();
    }
    return [];
  }

  /**
   * Retrieve the outstanding balance for an invoice.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to retrieve information for.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|int|mixed
   *   Return the outstanding amount.
   */
  public function getInvoiceBalance(Invoice $invoice) {
    $paidAmount = 0;

    foreach ($this->getInvoicePaymentAmounts($invoice) as $payment) {
      $paidAmount += $payment->se_pa_lines_amount;
    }

    return $invoice->se_in_total->value - $paidAmount;
  }

}
