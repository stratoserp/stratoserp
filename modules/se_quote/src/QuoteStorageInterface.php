<?php

namespace Drupal\se_quote;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_quote\Entity\QuoteInterface;

/**
 * Defines the storage handler class for Quote entities.
 *
 * This extends the base storage class, adding required special handling for
 * Quote entities.
 *
 * @ingroup se_quote
 */
interface QuoteStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Quote revision IDs for a specific Quote.
   *
   * @param \Drupal\se_quote\Entity\QuoteInterface $entity
   *   The Quote entity.
   *
   * @return int[]
   *   Quote revision IDs (in ascending order).
   */
  public function revisionIds(QuoteInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Quote author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Quote revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_quote\Entity\QuoteInterface $entity
   *   The Quote entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(QuoteInterface $entity);

  /**
   * Unsets the language for all Quote with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
