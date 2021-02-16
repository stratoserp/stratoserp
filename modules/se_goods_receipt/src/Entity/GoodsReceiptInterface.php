<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Goods Receipt entities.
 *
 * @ingroup se_goods_receipt
 */
interface GoodsReceiptInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Goods Receipt name.
   *
   * @return string
   *   Name of the Goods Receipt.
   */
  public function getName();

  /**
   * Sets the Goods Receipt name.
   *
   * @param string $name
   *   The Goods Receipt name.
   *
   * @return \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   *   The called Goods Receipt entity.
   */
  public function setName($name);

  /**
   * Gets the Goods Receipt creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Goods Receipt.
   */
  public function getCreatedTime();

  /**
   * Sets the Goods Receipt creation timestamp.
   *
   * @param int $timestamp
   *   The Goods Receipt creation timestamp.
   *
   * @return \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   *   The called Goods Receipt entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Goods Receipt revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Goods Receipt revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   *   The called Goods Receipt entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Goods Receipt revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Goods Receipt revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   *   The called Goods Receipt entity.
   */
  public function setRevisionUserId($uid);

}
