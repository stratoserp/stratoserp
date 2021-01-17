<?php

namespace Drupal\se_payment\Traits;

use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

/**
 * Trait to provide drupal payment utilities.
 */
trait ErpPaymentTrait {

  /**
   * Retrieve payments for passed invoice.
   *
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to retrieve information for.
   *
   * @return mixed
   *   Return an array of payment nodes.
   */
  public function getInvoicePayments(Node $invoice): array {
    // @todo Is there a nicer way to do this?
    $query = Database::getConnection()->select('node__se_pa_lines', 'nfpl');
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
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to retrieve information for.
   *
   * @return mixed
   *   Return an array of payment amounts.
   */
  private function getInvoicePaymentAmounts(Node $invoice): array {
    // @todo Is there a nicer way to do this?
    $query = Database::getConnection()->select('node__se_pa_lines', 'nfpl');
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
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to retrieve information for.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|int|mixed
   *   Return the outstanding amount.
   */
  public function getInvoiceBalance(Node $invoice) {
    $paidAmount = 0;

    foreach ($this->getInvoicePaymentAmounts($invoice) as $payment) {
      $paidAmount += $payment->se_pa_lines_amount;
    }

    return $invoice->se_in_total->value - $paidAmount;
  }

}
