<?php

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
   * Returns the Item published status indicator.
   *
   * Unpublished Item are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Item is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Item.
   *
   * @param bool $published
   *   TRUE to set this Item to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\se_item\Entity\ItemInterface
   *   The called Item entity.
   */
  public function setPublished($published);

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

}
