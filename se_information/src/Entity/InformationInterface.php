<?php

namespace Drupal\se_information\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Information entities.
 *
 * @ingroup se_information
 */
interface InformationInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Information name.
   *
   * @return string
   *   Name of the Information.
   */
  public function getName();

  /**
   * Sets the Information name.
   *
   * @param string $name
   *   The Information name.
   *
   * @return \Drupal\se_information\Entity\InformationInterface
   *   The called Information entity.
   */
  public function setName($name);

  /**
   * Gets the Information creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Information.
   */
  public function getCreatedTime();

  /**
   * Sets the Information creation timestamp.
   *
   * @param int $timestamp
   *   The Information creation timestamp.
   *
   * @return \Drupal\se_information\Entity\InformationInterface
   *   The called Information entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Information published status indicator.
   *
   * Unpublished Information are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Information is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Information.
   *
   * @param bool $published
   *   TRUE to set this Information to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\se_information\Entity\InformationInterface
   *   The called Information entity.
   */
  public function setPublished($published);

  /**
   * Gets the Information revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Information revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_information\Entity\InformationInterface
   *   The called Information entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Information revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Information revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_information\Entity\InformationInterface
   *   The called Information entity.
   */
  public function setRevisionUserId($uid);

}
