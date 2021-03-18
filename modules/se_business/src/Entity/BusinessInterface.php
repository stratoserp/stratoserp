<?php

declare(strict_types=1);

namespace Drupal\se_business\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Business entities.
 *
 * @ingroup se_business
 */
interface BusinessInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Business name.
   *
   * @return string
   *   Name of the Business.
   */
  public function getName();

  /**
   * Sets the Business name.
   *
   * @param string $name
   *   The Business name.
   *
   * @return \Drupal\se_business\Entity\BusinessInterface
   *   The called Business entity.
   */
  public function setName($name);

  /**
   * Gets the Business creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Business.
   */
  public function getCreatedTime();

  /**
   * Sets the Business creation timestamp.
   *
   * @param int $timestamp
   *   The Business creation timestamp.
   *
   * @return \Drupal\se_business\Entity\BusinessInterface
   *   The called Business entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix();

  /**
   * Gets the Business revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Business revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_business\Entity\BusinessInterface
   *   The called Business entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Business revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Business revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_business\Entity\BusinessInterface
   *   The called Business entity.
   */
  public function setRevisionUserId($uid);

}
