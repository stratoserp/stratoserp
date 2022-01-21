<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provide services for payment lines.
 */
class PaymentLineService {

  /**
   * Calculate the total an entity with payment lines.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to update the totals for..
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The updated entity.
   */
  function calculateTotal(EntityInterface $payment) {
    $total = 0;

    // Loop through the payment lines to calculate total.
    foreach ($payment->se_payment_lines as $paymentLine) {
      $total += $paymentLine->amount;
    }

    $payment->se_total->value = $total;
  }

}
