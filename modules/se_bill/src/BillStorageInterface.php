<?php

namespace Drupal\se_bill;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_bill\Entity\BillInterface;

/**
 * Defines the storage handler class for Bill entities.
 *
 * This extends the base storage class, adding required special handling for
 * Bill entities.
 *
 * @ingroup se_bill
 */
interface BillStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Bill revision IDs for a specific Bill.
   *
   * @param \Drupal\se_bill\Entity\BillInterface $entity
   *   The Bill entity.
   *
   * @return int[]
   *   Bill revision IDs (in ascending order).
   */
  public function revisionIds(BillInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Bill author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Bill revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_bill\Entity\BillInterface $entity
   *   The Bill entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BillInterface $entity);

  /**
   * Unsets the language for all Bill with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
