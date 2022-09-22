<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Entity;

use Drupal\se_item\Entity\Item;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Timekeeping entities.
 *
 * @ingroup se_timekeeping
 */
interface TimekeepingInterface extends StratosEntityBaseInterface {

  /**
   * Return the item associated with the timekeeping entry.
   *
   * @return \Drupal\se_item\Entity\Item
   *   Return the item for the timekeeping entry.
   */
  public function getItem(): Item;

}
