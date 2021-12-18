<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Subscription entities.
 *
 * @ingroup se_subscription
 */
interface SubscriptionInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Subscription name.
   *
   * @return string
   *   Name of the Subscription.
   */
  public function getName();

  /**
   * Sets the Subscription name.
   *
   * @param string $name
   *   The Subscription name.
   *
   * @return \Drupal\se_subscription\Entity\SubscriptionInterface
   *   The called Subscription entity.
   */
  public function setName($name);

  /**
   * Gets the Subscription creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Subscription.
   */
  public function getCreatedTime();

  /**
   * Sets the Subscription creation timestamp.
   *
   * @param int $timestamp
   *   The Subscription creation timestamp.
   *
   * @return \Drupal\se_subscription\Entity\SubscriptionInterface
   *   The called Subscription entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix();

  /**
   * Gets the Subscription revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Subscription revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_subscription\Entity\SubscriptionInterface
   *   The called Subscription entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Subscription revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Subscription revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_subscription\Entity\SubscriptionInterface
   *   The called Subscription entity.
   */
  public function setRevisionUserId($uid);

}
