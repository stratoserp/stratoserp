<?php

declare(strict_types=1);

namespace Drupal\se_business\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Business entities.
 *
 * @ingroup se_business
 */
interface BusinessInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Retrieve the current Business balance.
   *
   * @return int
   *   The current balance.
   */
  public function getBalance(): int;

  /**
   * Set the current Business balance.
   *
   * @param int $value
   *   The amount to set the balance to.
   *
   * @return int
   *   The current balance.
   */
  public function setBalance(int $value): int;

  /**
   * Adjust the Business balance.
   *
   * @param int $value
   *   The amount to set the balance to.
   *
   * @return int
   *   The current balance.
   */
  public function adjustBalance(int $value): int;

}
