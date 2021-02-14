<?php

namespace Drupal\se_supplier;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_supplier\Entity\SupplierInterface;

/**
 * Defines the storage handler class for Supplier entities.
 *
 * This extends the base storage class, adding required special handling for
 * Supplier entities.
 *
 * @ingroup se_supplier
 */
interface SupplierStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Supplier revision IDs for a specific Supplier.
   *
   * @param \Drupal\se_supplier\Entity\SupplierInterface $entity
   *   The Supplier entity.
   *
   * @return int[]
   *   Supplier revision IDs (in ascending order).
   */
  public function revisionIds(SupplierInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Supplier author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Supplier revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_supplier\Entity\SupplierInterface $entity
   *   The Supplier entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(SupplierInterface $entity);

  /**
   * Unsets the language for all Supplier with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
