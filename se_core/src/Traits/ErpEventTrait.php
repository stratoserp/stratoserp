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
   * Add a flag to an invoice so that future events are not processed on it.
   *
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to adjust.
   */
  public function setSkipCustomerXeroEvents(Node $invoice) {
    $invoice->skipCustomerXeroEvents = TRUE;
  }

  /**
   * Return the amount outstanding for an invoice.
   *
   * @param Node $invoice
   *   The invoice node to return the balance of.
   */
  public function getInvoiceOutstanding($invoice) {

  }

}
