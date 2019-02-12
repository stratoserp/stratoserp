<?php

namespace Drupal\se_document;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_document\Entity\SeDocumentInterface;

/**
 * Defines the storage handler class for Document entities.
 *
 * This extends the base storage class, adding required special handling for
 * Document entities.
 *
 * @ingroup se_document
 */
interface SeDocumentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Document revision IDs for a specific Document.
   *
   * @param \Drupal\se_document\Entity\SeDocumentInterface $entity
   *   The Document entity.
   *
   * @return int[]
   *   Document revision IDs (in ascending order).
   */
  public function revisionIds(SeDocumentInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Document author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Document revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_document\Entity\SeDocumentInterface $entity
   *   The Document entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(SeDocumentInterface $entity);

  /**
   * Unsets the language for all Document with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
