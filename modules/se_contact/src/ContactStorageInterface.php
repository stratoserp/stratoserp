<?php

declare(strict_types=1);

namespace Drupal\se_contact;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_contact\Entity\ContactInterface;

/**
 * Defines the storage handler class for Contact entities.
 *
 * This extends the base storage class, adding required special handling for
 * Contact entities.
 *
 * @ingroup se_contact
 */
interface ContactStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Contact revision IDs for a specific Contact.
   *
   * @param \Drupal\se_contact\Entity\ContactInterface $entity
   *   The Contact entity.
   *
   * @return int[]
   *   Contact revision IDs (in ascending order).
   */
  public function revisionIds(ContactInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Contact author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Contact revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_contact\Entity\ContactInterface $entity
   *   The Contact entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ContactInterface $entity);

  /**
   * Unsets the language for all Contact with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
