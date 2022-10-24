<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

/**
 * Class for base entities with item lines.
 */
abstract class StratosLinesEntityBase extends StratosEntityBase implements StratosLinesEntityBaseInterface {

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_total->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTotal(int $value): int {
    return $this->se_total->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTax(): int {
    return (int) $this->se_tax->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTax(int $value): int {
    return $this->se_tax->value = $value;
  }

}
