<?php

namespace Drupal\se_payment\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;

/**
 * Simplme payment service.
 */
class PaymentService {

  /**
   * Update invoices in the payment.
   *
   * Loop through the payment entries and mark the invoices as
   * paid/unpaid as dictated by the parameter.
   *
   * @param \Drupal\se_payment\Entity\Payment $payment
   *   The payment to work through.
   * @param bool $paid
   *   Whether the invoices should be marked paid, ot not.
   *
   * @return int
   *   The new payment amount.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateInvoices(Payment $payment, bool $paid = TRUE): int {
    $amount = 0;
    foreach ($payment->se_payment_lines as $paymentLine) {
      // Don't try on operate on invoices with no payment.
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      if (!empty($paymentLine->amount)
        && $invoice = Invoice::load($paymentLine->target_id)) {
        // Set a dynamic field on the entity so that other events dont try and
        // do things that we will take care of once the save is complete.
        // This avoids re-calculating the total outstanding for a customer, for
        // example.
        //
        // $event->stopPropagation() didn't appear to work for this.
        //
        // This event will save the invoice, avoid an invoice save in any other
        // triggered events.
        $invoice->setSkipInvoiceSaveEvents();

        // This event updates the total, avoid it in other triggered events.
        /** @var \Drupal\se_business\Entity\Business $business */
        $business = \Drupal::service('se_business.service')->lookupBusiness($invoice);
        $business->setSkipBusinessXeroEvents(TRUE);

        $invoice->set('se_status_ref', \Drupal::service('se_invoice.service')->checkInvoiceStatus($invoice, $paymentLine->amount));
        $invoice->set('se_outstanding', $invoice->getInvoiceBalance());
        $invoice->save();

        $amount += $paymentLine->amount;
      }
    }

    if ($paid) {
      $amount *= -1;
    }

    return (int) $amount;
  }

  /**
   * On payment, update the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The business entity to update the balance of.
   * @param int $amount
   *   The amount to set the balance to in cents.
   *
   * @return void|int
   *   New balance.
   */
  public function updateBusinessBalance(EntityInterface $entity, int $amount): ?int {
    if ($amount === 0) {
      return 0;
    }

    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($entity)) {
      \Drupal::logger('se_business_accounting_save')->error('No business set for %entity', ['%entity' => $entity->id()]);
      return 0;
    }

    return \Drupal::service('se_business.service')->adjustBalance($business, $amount);
  }

}
