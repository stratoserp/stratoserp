<?php

declare(strict_types=1);

namespace Drupal\se_payment\Service;

use Drupal\se_payment\Entity\Payment;

/**
 * Interface definition for payment service.
 */
interface PaymentServiceInterface {

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
  public function updateInvoices(Payment $payment): void;

}
