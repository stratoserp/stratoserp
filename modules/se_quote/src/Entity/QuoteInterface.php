<?php

declare(strict_types=1);

namespace Drupal\se_quote\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\se_business\Entity\Business;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Quote entities.
 *
 * @ingroup se_quote
 */
interface QuoteInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Quote name.
   *
   * @return string
   *   Name of the Quote.
   */
  public function getName();

  /**
   * Sets the Quote name.
   *
   * @param string $name
   *   The Quote name.
   *
   * @return \Drupal\se_quote\Entity\QuoteInterface
   *   The called Quote entity.
   */
  public function setName($name);

  /**
   * Gets the Quote creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Quote.
   */
  public function getCreatedTime();

  /**
   * Sets the Quote creation timestamp.
   *
   * @param int $timestamp
   *   The Quote creation timestamp.
   *
   * @return \Drupal\se_quote\Entity\QuoteInterface
   *   The called Quote entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix();

  /**
   * Gets the Quote revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Quote revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\se_quote\Entity\QuoteInterface
   *   The called Quote entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Quote revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Quote revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\se_quote\Entity\QuoteInterface
   *   The called Quote entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Return the total quote value.
   */
  public function getTotal(): int;

  /**
   * Return the business associated with the quote.
   */
  public function getBusiness(): Business;

}
