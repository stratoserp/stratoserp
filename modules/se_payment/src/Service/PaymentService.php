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
    foreach ($reconcileList as $invoice) {
      $invoice->setSkipSaveEvents();
      $invoice->save();
    }

    /** @var \Drupal\se_customer\Entity\Customer $customer */
    $customer = $payment->se_cu_ref->entity;
    $customer->adjustBalance($previousTotal - $newTotal);
  }

  /**
   * Build the list of invoices that were on the payment before.
   */
  private function loadOldInvoices(Payment $payment): array {
    $reconcileList = [];
    $amount = 0;

    if ($oldPayment = $payment->getOldPayment()) {
      foreach ($oldPayment->se_payment_lines as $paymentLine) {
        if ($invoice = Invoice::load($paymentLine->target_id)) {
          $reconcileList[$invoice->id()] = $invoice;
          $amount += $paymentLine->amount;
        }
        else {
          \Drupal::logger('se_payment')
            ->error('Error loading invoice apparently in payment, this is very bad.');
        }
      }
    }

    return [$reconcileList, $amount];
  }

}
