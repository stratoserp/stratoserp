<?php

namespace Drupal\se_purchase_order\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining PurchaseOrder entities.
 *
 * @ingroup se_purchase_order
 */
interface PurchaseOrderInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the PurchaseOrder name.
   *
   * @return string
   *   Name of the PurchaseOrder.
   */
  public function getName();

  /**
   * Sets the PurchaseOrder name.
   *
   * @param string $name
   *   The PurchaseOrder name.
   *
   * @return \Drupal\se_purchase_order\Entity\PurchaseOrderInterface
   *   The called PurchaseOrder entity.
   */
  public function setName($name);

  /**
   * Gets the PurchaseOrder creation timestamp.
   *
   * @return int
   *   Creation timestamp of the PurchaseOrder.
   */
  public function getCreatedTime();

  /**
   * Sets the PurchaseOrder creation timestamp.
   *
   * @param int $timestamp
   *   The PurchaseOrder creation timestamp.
   *
   * @return \Drupal\se_purchase_order\Entity\PurchaseOrderInterface
   *   The called PurchaseOrder entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the PurchaseOrder revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the PurchaseOrder revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_purchase_order\Entity\PurchaseOrderInterface
   *   The called PurchaseOrder entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the PurchaseOrder revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the PurchaseOrder revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_purchase_order\Entity\PurchaseOrderInterface
   *   The called PurchaseOrder entity.
   */
  public function setRevisionUserId($uid);

}
