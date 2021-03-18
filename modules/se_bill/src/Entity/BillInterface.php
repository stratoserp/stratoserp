<?php

declare(strict_types=1);

namespace Drupal\se_bill\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Bill entities.
 *
 * @ingroup se_bill
 */
interface BillInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Bill name.
   *
   * @return string
   *   Name of the Bill.
   */
  public function getName();

  /**
   * Sets the Bill name.
   *
   * @param string $name
   *   The Bill name.
   *
   * @return \Drupal\se_bill\Entity\BillInterface
   *   The called Bill entity.
   */
  public function setName($name);

  /**
   * Gets the Bill creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Bill.
   */
  public function getCreatedTime();

  /**
   * Sets the Bill creation timestamp.
   *
   * @param int $timestamp
   *   The Bill creation timestamp.
   *
   * @return \Drupal\se_bill\Entity\BillInterface
   *   The called Bill entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix();

  /**
   * Gets the Bill revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Bill revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_bill\Entity\BillInterface
   *   The called Bill entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Bill revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Bill revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_bill\Entity\BillInterface
   *   The called Bill entity.
   */
  public function setRevisionUserId($uid);

}
