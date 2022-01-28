<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

/**
 * Class for base entities with item lines.
 */
abstract class StratosLinesEntityBase extends StratosEntityBase implements StratosLinesEntityBaseInterface {

  /**
   * Storage for item lines during save process.
   */
  private $itemLineStorage;

  /**
   * {@inheritdoc}
   */
  public function storeOldLines(): void {
    $this->itemLineStorage = $this->se_item_lines;
  }

  /**
   * {@inheritdoc}
   */
  public function getOldLines() {
    return $this->itemLineStorage ?: [];
  }

}
