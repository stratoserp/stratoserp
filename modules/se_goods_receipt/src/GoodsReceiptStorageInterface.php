<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_goods_receipt\Entity\GoodsReceiptInterface;

/**
 * Defines the storage handler class for Goods Receipt entities.
 *
 * This extends the base storage class, adding required special handling for
 * Goods Receipt entities.
 *
 * @ingroup se_goods_receipt
 */
interface GoodsReceiptStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Goods Receipt revision IDs for a specific Goods Receipt.
   *
   * @param \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface $entity
   *   The Goods Receipt entity.
   *
   * @return int[]
   *   Goods Receipt revision IDs (in ascending order).
   */
  public function revisionIds(GoodsReceiptInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Goods Receipt author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Goods Receipt revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface $entity
   *   The Goods Receipt entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(GoodsReceiptInterface $entity);

  /**
   * Unsets the language for all Goods Receipt with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
