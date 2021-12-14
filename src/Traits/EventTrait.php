<?php

namespace Drupal\stratoserp\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_invoice\Entity\Invoice;

/**
 * Trait to provide drupal event utilities.
 *
 * When saving an invoice or business, there are times when it makes sense
 * to skip further events.
 *
 * @package Drupal\stratoserp\Traits
 */
trait EventTrait {

  /**
   * Add a flag to an invoice so that future events are not processed on it.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to adjust.
   */
  public function setSkipInvoiceSaveEvents(Invoice $invoice): void {
    $invoice->skipInvoiceSaveEvents = TRUE;
  }

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to adjust.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipInvoiceSaveEvents(Invoice $invoice): bool {
    return $invoice->skipInvoiceSaveEvents ?: FALSE;
  }

  /**
   * Add a flag to a business so that future events are not processed on it.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The invoice to adjust.
   */
  public function setSkipBusinessXeroEvents(Business $business): void {
    $business->skipBusinessXeroEvents = TRUE;
  }

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business to skip events.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipBusinessXeroEvents(Business $business): bool {
    return $business->skipBusinessXeroEvents ?: FALSE;
  }

}
