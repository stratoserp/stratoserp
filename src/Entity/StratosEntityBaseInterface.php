<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

interface StratosEntityBaseInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Entity name.
   *
   * @return string
   *   Name of the Entity.
   */
  public function getName();

  /**
   * Sets the Entity name.
   *
   * @param string $name
   *   The Entity name.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The called Entity entity.
   */
  public function setName($name);

  /**
   * Get the Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the creation timestamp.
   *
   * @param int $timestamp
   *   The Entity creation timestamp.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The called Entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Gets the Entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The called entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The called entity.
   */
  public function setRevisionUserId($uid);

}
