<?php

declare(strict_types=1);

namespace Drupal\se_customer\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Customer entities.
 *
 * @ingroup se_customer
 */
interface CustomerInterface extends StratosEntityBaseInterface {

  /**
   * Retrieve the current Customer balance.
   *
   * @return int
   *   The current balance.
   */
  public function getBalance(): int;

  /**
   * Set the current Customer balance.
   *
   * @param int $value
   *   The amount to set the balance to.
   *
   * @return int
   *   The current balance.
   */
  public function setBalance(int $value): int;

  /**
   * Adjust the Customer balance.
   *
   * @param int $value
   *   The amount to set the balance to.
   *
   * @return int
   *   The current balance.
   */
  public function adjustBalance(int $value): int;

  /**
   * Update the Customer balance from outstanding invoice amounts.
   */
  public function updateBalance(): int;

  /**
   * Add a flag to a customer so that future events are not processed on it.
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
