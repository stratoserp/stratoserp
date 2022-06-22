<?php

declare(strict_types=1);

namespace Drupal\se_item\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Item entities.
 *
 * @ingroup se_item
 */
interface ItemInterface extends StratosEntityBaseInterface {

  /**
   * Returns if the item is a stock item.
   *
   * @return bool
   *   Whether the item is a stock item or not.
   */
  public function isStock(): bool;

  /**
   * Returns if the item is a bulk stock item.
   *
   * @return bool
   *   Whether the item is a stock item or not.
   */
  public function isBulkStock(): bool;

  /**
   * Returns whether the item has a parent.
   *
   * Stock items with no parent are used for quoting and for items that
   * do not need to be serial number tracked.
   *
   * @return bool
   *   Whether the item has a parent.
   */
  public function hasParent(): bool;

}
