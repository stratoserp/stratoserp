<?php

declare(strict_types=1);

namespace Drupal\se_payment\Service;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;

/**
 * Simple payment service.
 */
class PaymentService implements PaymentServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function updateInvoices(Payment $payment): void {
    $reconcileList = $this->loadOldInvoices($payment);

    foreach ($payment->se_payment_lines as $paymentLine) {
      // Don't try on operate on invoices with no payment.
      if (!$paymentLine->amount) {
        continue;
      }

      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      $invoice = $reconcileList[$paymentLine->target_id]
        ?? Invoice::load($paymentLine->target_id);

      // Re-apply the payments as we update the invoice.
      $invoice->setOutstanding($invoice->getOutstanding() - (int) $paymentLine->amount);
      $reconcileList[$invoice->id()] = $invoice;
    }

    // Loop through the invoices and save them all now.
    foreach ($reconcileList as $invoice) {
      $invoice->setSkipSaveEvents();
      $invoice->save();
    }
  }

  /**
   * Build the list of invoices that were on the payment before.
   */
  private function loadOldInvoices(Payment $payment): array {
    $reconcileList = [];

    if (!$oldPayment = $payment->getOldPayment()) {
      return [];
    }

    foreach ($oldPayment->se_payment_lines as $paymentLine) {
      $invoice = $reconcileList[$paymentLine->target_id]
        ?? Invoice::load($paymentLine->target_id);

      // Reverse the payments as we load the old invoices.
      $invoice->setOutstanding($invoice->getOutstanding() + $paymentLine->amount);
      $reconcileList[$invoice->id()] = $invoice;
    }

    return $reconcileList;
  }

}
