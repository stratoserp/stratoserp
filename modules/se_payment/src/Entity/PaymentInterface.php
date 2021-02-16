<?php

declare(strict_types=1);

namespace Drupal\se_payment\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Payment entities.
 *
 * @ingroup se_payment
 */
interface PaymentInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Payment name.
   *
   * @return string
   *   Name of the Payment.
   */
  public function getName();

  /**
   * Sets the Payment name.
   *
   * @param string $name
   *   The Payment name.
   *
   * @return \Drupal\se_payment\Entity\PaymentInterface
   *   The called Payment entity.
   */
  public function setName($name);

  /**
   * Gets the Payment creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Payment.
   */
  public function getCreatedTime();

  /**
   * Sets the Payment creation timestamp.
   *
   * @param int $timestamp
   *   The Payment creation timestamp.
   *
   * @return \Drupal\se_payment\Entity\PaymentInterface
   *   The called Payment entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Payment revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Payment revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_payment\Entity\PaymentInterface
   *   The called Payment entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Payment revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Payment revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_payment\Entity\PaymentInterface
   *   The called Payment entity.
   */
  public function setRevisionUserId($uid);

}
