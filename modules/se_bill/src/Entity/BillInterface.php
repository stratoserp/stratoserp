<?php

declare(strict_types=1);

namespace Drupal\se_bill\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Bill entities.
 *
 * @ingroup se_bill
 */
interface BillInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix for this entity type.
   */
  public function getSearchPrefix(): string;

  /**
   * Return the total amount of the entity.
   */
  public function getTotal(): int;

}
