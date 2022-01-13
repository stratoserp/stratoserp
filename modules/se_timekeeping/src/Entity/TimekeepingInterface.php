<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Timekeeping entities.
 *
 * @ingroup se_timekeeping
 */
interface TimekeepingInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Timekeeping name.
   *
   * @return string
   *   Name of the Timekeeping.
   */
  public function getName();

  /**
   * Sets the Timekeeping name.
   *
   * @param string $name
   *   The Timekeeping name.
   *
   * @return \Drupal\se_timekeeping\Entity\TimekeepingInterface
   *   The called Timekeeping entity.
   */
  public function setName($name);

  /**
   * Gets the Timekeeping creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Timekeeping.
   */
  public function getCreatedTime();

  /**
   * Sets the Timekeeping creation timestamp.
   *
   * @param int $timestamp
   *   The Timekeeping creation timestamp.
   *
   * @return \Drupal\se_timekeeping\Entity\TimekeepingInterface
   *   The called Timekeeping entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix();

  /**
   * Gets the Timekeeping revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Timekeeping revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_timekeeping\Entity\TimekeepingInterface
   *   The called Timekeeping entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Timekeeping revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Timekeeping revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_timekeeping\Entity\TimekeepingInterface
   *   The called Timekeeping entity.
   */
  public function setRevisionUserId($uid);

}
