<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

/**
 * Interface for base entities with item lines.
 */
interface StratosLinesEntityBaseInterface extends StratosEntityBaseInterface {

  /**
   * Store the current invoice lines for later comparison in the save process.
   */
  public function storeOldLines(): void;

  /**
   * Retrieve the stored lines for comparison in the save process.
   */
  public function getOldLines();

}
