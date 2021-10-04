<?php

declare(strict_types=1);

namespace Drupal\se_item\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Item entities.
 *
 * @ingroup se_item
 */
interface ItemInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Item name.
   *
   * @return string
   *   Name of the Item.
   */
  public function getName();

  /**
   * Sets the Item name.
   *
   * @param string $name
   *   The Item name.
   *
   * @return \Drupal\se_item\Entity\ItemInterface
   *   The called Item entity.
   */
  public function setName($name);

  /**
   * Gets the Item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Item.
   */
  public function getCreatedTime();

  /**
   * Sets the Item creation timestamp.
   *
   * @param int $timestamp
   *   The Item creation timestamp.
   *
   * @return \Drupal\se_item\Entity\ItemInterface
   *   The called Item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix();

  /**
   * Gets the Item revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Item revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_item\Entity\ItemInterface
   *   The called Item entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Item revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Item revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_item\Entity\ItemInterface
   *   The called Item entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Returns if the item is a stock item.
   *
   * @return bool
   *   Whether the item is a stock item or not.
   */
  public function isStock(): bool;

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
