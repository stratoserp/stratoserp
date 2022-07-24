<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Service;

use Drupal\se_payment\Entity\Payment;

/**
 * Provide services for payment lines.
 */
class PaymentLineService {

  /**
   * Calculate the total an entity with payment lines.
   *
   * @param \Drupal\se_payment\Entity\Payment $payment
   *   Payment entity to update the totals for.
   */
  public function calculateTotal(Payment $payment) {
    $total = 0;

    // Loop through the payment lines to calculate total.
    foreach ($payment->se_payment_lines as $paymentLine) {
      $total += $paymentLine->amount;
    }

    $payment->setTotal($total);
  }

}
