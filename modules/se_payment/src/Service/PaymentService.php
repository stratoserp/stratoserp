<?php

namespace Drupal\se_payment\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;

/**
 * Simple payment service.
 */
class PaymentService {

  /**
   * Update the statuses of the related invoices when a payment is saved.
   *
   * Load the invoices that were in the payment from pre-save and get total.
   * Work through the list now in the payment and get total.
   * Finally, save them all and return the total for update.
   * This means only a single save is required for each invoice.
   *
   * @param \Drupal\se_payment\Entity\Payment $payment
   *   The payment to work through.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateInvoices(Payment $payment): void {
    [$reconcileList, $previousTotal] = $this->loadOldInvoices($payment);

    $newTotal = 0;
    foreach ($payment->se_payment_lines as $paymentLine) {
      // Don't try on operate on invoices with no payment.
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      if (!empty($paymentLine->amount)
        && $invoice = Invoice::load($paymentLine->target_id)) {

        $reconcileList[$invoice->id()] = $invoice;
        $newTotal += $paymentLine->amount;
      }
    }

    // Loop through the invoices and save them all now.
    foreach ($reconcileList as $item) {
      $item->save();
    }

    $payment->se_bu_ref->entity->adjustBalance($previousTotal - $newTotal);
  }

  /**
   * Set all invoices completely unpaid, this will be reconciled later.
   */
  private function loadOldInvoices(Payment $payment): array {
    $reconcileList = [];
    $amount = 0;

    foreach ($payment->getOldPayments() as $paymentLine) {
      $invoice = Invoice::load($paymentLine->target_id);
      $reconcileList[$invoice->id()] = $invoice;
      $amount += $paymentLine->amount;
    }

    return [$reconcileList, $amount];
  }

}
