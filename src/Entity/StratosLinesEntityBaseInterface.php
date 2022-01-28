<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface for base entities with item lines.
 */
interface StratosLinesEntityBaseInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Store the current invoice lines for later comparison in the save process.
   */
  public function storeOldLines(): void;

  /**
   * Retrieve the stored lines for comparison in the save process.
   */
  public function getOldLines();

}
