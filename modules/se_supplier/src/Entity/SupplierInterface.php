<?php

namespace Drupal\se_supplier\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Supplier entities.
 *
 * @ingroup se_supplier
 */
interface SupplierInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Supplier name.
   *
   * @return string
   *   Name of the Supplier.
   */
  public function getName();

  /**
   * Sets the Supplier name.
   *
   * @param string $name
   *   The Supplier name.
   *
   * @return \Drupal\se_supplier\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setName($name);

  /**
   * Gets the Supplier creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Supplier.
   */
  public function getCreatedTime();

  /**
   * Sets the Supplier creation timestamp.
   *
   * @param int $timestamp
   *   The Supplier creation timestamp.
   *
   * @return \Drupal\se_supplier\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Supplier revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Supplier revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_supplier\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Supplier revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Supplier revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_supplier\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setRevisionUserId($uid);

}
