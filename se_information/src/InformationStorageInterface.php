<?php

namespace Drupal\se_information;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_information\Entity\InformationInterface;

/**
 * Defines the storage handler class for Information entities.
 *
 * This extends the base storage class, adding required special handling for
 * Information entities.
 *
 * @ingroup se_information
 */
interface InformationStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Information revision IDs for a specific Information.
   *
   * @param \Drupal\se_information\Entity\InformationInterface $entity
   *   The Information entity.
   *
   * @return int[]
   *   Information revision IDs (in ascending order).
   */
  public function revisionIds(InformationInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Information author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Information revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_information\Entity\InformationInterface $entity
   *   The Information entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(InformationInterface $entity);

  /**
   * Unsets the language for all Information with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
