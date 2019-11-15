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
  public function getInvoicePayments(Node $invoice) {
    // TODO? Is there a nicer way to do this?
    $database = Database::getConnection();
    $query = $database->select('node__field_pa_lines', 'nfpl');
    $query->fields('nfpl', ['entity_id']);
    $query->condition('nfpl.field_pa_lines_target_id', $invoice->id());
    $payments = $query->execute()->fetchAll();

    return $payments;
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
  public function getInvoicePaymentAmounts(Node $invoice) {
    // TODO? Is there a nicer way to do this?
    $database = Database::getConnection();
    $query = $database->select('node__field_pa_lines', 'nfpl');
    $query->fields('nfpl', ['field_pa_lines_amount']);
    $query->condition('nfpl.field_pa_lines_target_id', $invoice->id());
    $payments = $query->execute()->fetchAll();

    return $payments;
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
    $paid_amount = 0;

    foreach ($this->getInvoicePaymentAmounts($invoice) as $payment) {
      $paid_amount += $payment->field_pa_lines_amount;
    }

    return $invoice->field_in_total->value - $paid_amount;
  }

}