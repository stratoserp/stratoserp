<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

/**
 * Interface for base entities with item lines.
 */
interface StratosLinesEntityBaseInterface extends StratosEntityBaseInterface {

  /**
   * Retrieve the total for the entity.
   *
   * @return int
   *   The total of the entity.
   */
  public function getTotal(): int;

  /**
   * Set the total for the entity.
   *
   * @param int $value
   *   The total value to store for the entity.
   *
   * @return int
   *   The total set on the entity.
   */
  public function setTotal(int $value): int;

  /**
   * Retrieve the tax for the entity.
   *
   * @return int
   *   The tax of the entity.
   */
  public function getTax(): int;

  /**
   * Set the tax for the entity.
   *
   * @param int $value
   *   The tax value to store for the entity.
   *
   * @return int
   *   The tax set on the entity.
   */
  public function setTax(int $value): int;

}
