<?php

namespace Drupal\se_stock_item\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Stock item entities.
 *
 * @ingroup se_stock_item
 */
interface StockItemInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Stock item name.
   *
   * @return string
   *   Name of the Stock item.
   */
  public function getName();

  /**
   * Sets the Stock item name.
   *
   * @param string $name
   *   The Stock item name.
   *
   * @return \Drupal\se_stock_item\Entity\StockItemInterface
   *   The called Stock item entity.
   */
  public function setName($name);

  /**
   * Gets the Stock item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Stock item.
   */
  public function getCreatedTime();

  /**
   * Sets the Stock item creation timestamp.
   *
   * @param int $timestamp
   *   The Stock item creation timestamp.
   *
   * @return \Drupal\se_stock_item\Entity\StockItemInterface
   *   The called Stock item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Stock item published status indicator.
   *
   * Unpublished Stock item are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Stock item is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Stock item.
   *
   * @param bool $published
   *   TRUE to set this Stock item to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\se_stock_item\Entity\StockItemInterface
   *   The called Stock item entity.
   */
  public function setPublished($published);

}
