<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Invoice entities.
 *
 * @ingroup se_invoice
 */
interface InvoiceInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Return the total invoice value.
   */
  public function getTotal(): int;

  /**
   * Return the unpaid invoice value.
   */
  public function getOutstanding(): int;

  /**
   * Retrieve the outstanding balance for an invoice.
   */
  public function getInvoiceBalance(): int;

  /**
   * Add a flag to an invoice so that future events are not processed on it.
   *
   * @param bool $value
   *   The new value to set the flag to, defaults to TRUE.
   */
  public function setSkipInvoiceSaveEvents(bool $value): void;

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @return bool
   *   Return the current setting.
   */
  public function getSkipInvoiceSaveEvents(): bool;

}
