<?php

declare(strict_types=1);

namespace Drupal\se_supplier\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Supplier entities.
 *
 * @ingroup se_supplier
 */
interface SupplierInterface extends StratosEntityBaseInterface {

  /**
   * Retrieve the current Supplier balance.
   *
   * @return int
   *   The current balance.
   */
  public function getBalance(): int;

  /**
   * Set the current Supplier balance.
   *
   * @param int $value
   *   The amount to set the balance to.
   *
   * @return int
   *   The current balance.
   */
  public function setBalance(int $value): int;

  /**
   * Adjust the Supplier balance.
   *
   * @param int $value
   *   The amount to set the balance to.
   *
   * @return int
   *   The current balance.
   */
  public function adjustBalance(int $value): int;

  /**
   * Add a flag to a supplier so that future events are not processed on it.
   *
   * Specific to Xero integration, so maybe could be handled better.
   */
  public function setSkipXeroEvents(): void;

  /**
   * Retrieve whether to skip invoice save events.
   *
   * Specific to Xero integration, so maybe could be handled better.
   *
   * @return bool
   *   Return the current setting.
   */
  public function isSkipXeroEvents(): bool;

}
