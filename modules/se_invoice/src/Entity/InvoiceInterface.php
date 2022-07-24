<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Provides an interface for defining Invoice entities.
 *
 * @ingroup se_invoice
 */
interface InvoiceInterface extends StratosLinesEntityBaseInterface {

  /**
   * Return the unpaid invoice value.
   */
  public function getOutstanding(): int;

  /**
   * Set the unpaid invoice value.
   */
  public function setOutstanding(int $value): int;

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
  public function setSkipSaveEvents(bool $value): void;

  /**
   * Retrieve whether to skip invoice save events.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipSaveEvents(): bool;

  /**
   * Store the old invoice in the presave process.
   */
  public function storeOldInvoice(): void;

  /**
   * Retrieve the old invoice when in the presave process.
   *
   * @return \Drupal\se_invoice\Entity\Invoice|null
   *   The stored invoice, or NULL.
   */
  public function getOldInvoice(): ?Invoice;

}
