<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Timekeeping entities.
 *
 * @ingroup se_timekeeping
 */
interface TimekeepingInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

}
