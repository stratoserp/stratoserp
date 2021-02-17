<?php

declare(strict_types=1);

namespace Drupal\se_business;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_business\Entity\BusinessInterface;

/**
 * Defines the storage handler class for Business entities.
 *
 * This extends the base storage class, adding required special handling for
 * Business entities.
 *
 * @ingroup se_business
 */
interface BusinessStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Business revision IDs for a specific Business.
   *
   * @param \Drupal\se_business\Entity\BusinessInterface $entity
   *   The Business entity.
   *
   * @return int[]
   *   Business revision IDs (in ascending order).
   */
  public function revisionIds(BusinessInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Business author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Business revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_business\Entity\BusinessInterface $entity
   *   The Business entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BusinessInterface $entity);

  /**
   * Unsets the language for all Business with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
