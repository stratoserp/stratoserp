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
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      if (!empty($paymentLine->amount)
      && $invoice = Invoice::load($paymentLine->target_id)) {

        $invoice->setOutstanding($invoice->getOutstanding() - $paymentLine->amount);
        $reconcileList[$invoice->id()] = $invoice;
      }
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

    if ($oldPayment = $payment->getOldPayment()) {
      foreach ($oldPayment->se_payment_lines as $paymentLine) {
        if ($invoice = Invoice::load($paymentLine->target_id)) {
          $reconcileList[$invoice->id()] = $invoice;
        }
        else {
          \Drupal::logger('se_payment')
            ->error('Error loading invoice apparently in payment, this is very bad.');
        }
      }
    }

    return $reconcileList;
  }

}
