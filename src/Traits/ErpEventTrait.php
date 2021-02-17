<?php

namespace Drupal\stratoserp\Traits;

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
  public function setSkipInvoiceSaveEvents(Node $invoice): void {
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
  public function isSkipInvoiceSaveEvents(Node $invoice): bool {
    return $invoice->skipInvoiceSaveEvents ?: TRUE;
  }

  /**
   * Add a flag to an invoice so that future events are not processed on it.
   *
   * @param \Drupal\node\Entity\Node $business
   *   The invoice to adjust.
   */
  public function setSkipBusinessXeroEvents(Node $business): void {
    $business->skipBusinessXeroEvents = TRUE;
  }

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @param \Drupal\node\Entity\Node $business
   *   The business to skip events.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipBusinessXeroEvents(Node $business): bool {
    return $business->skipBusinessXeroEvents ?: TRUE;
  }

}
