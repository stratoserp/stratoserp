<?php

namespace Drupal\se_purchase_order;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_purchase_order\Entity\PurchaseOrderInterface;

/**
 * Defines the storage handler class for PurchaseOrder entities.
 *
 * This extends the base storage class, adding required special handling for
 * PurchaseOrder entities.
 *
 * @ingroup se_purchase_order
 */
interface PurchaseOrderStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of PurchaseOrder revision IDs for a specific PurchaseOrder.
   *
   * @param \Drupal\se_purchase_order\Entity\PurchaseOrderInterface $entity
   *   The PurchaseOrder entity.
   *
   * @return int[]
   *   PurchaseOrder revision IDs (in ascending order).
   */
  public function revisionIds(PurchaseOrderInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as PurchaseOrder author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   PurchaseOrder revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_purchase_order\Entity\PurchaseOrderInterface $entity
   *   The PurchaseOrder entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(PurchaseOrderInterface $entity);

  /**
   * Unsets the language for all PurchaseOrder with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
