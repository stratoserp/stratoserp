<?php

namespace Drupal\se_core\Traits;

use Drupal\node\Entity\Node;

/**
 * Trait to provide drupal event utilities.
 */
trait ErpEventTrait {

  /**
   * Add a flag to an invoice so that future events are not processed on it.
   *
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to adjust.
   */
  public function setSkipInvoiceSaveEvents(Node $invoice) {
    $invoice->skipInvoiceSaveEvents = TRUE;
  }

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to adjust.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipInvoiceSaveEvents(Node $invoice) {
    return $invoice->skipInvoiceSaveEvents;
  }

  /**
   * Add a flag to an invoice so that future events are not processed on it.
   *
   * @param \Drupal\node\Entity\Node $customer
   *   The invoice to adjust.
   */
  public function setSkipCustomerXeroEvents(Node $customer) {
    $customer->skipCustomerXeroEvents = TRUE;
  }

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @param \Drupal\node\Entity\Node $customer
   *   The customer to skip events.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipCustomerXeroEvents(Node $customer) {
    return $customer->skipInvoiceSaveEvents;
  }

}
