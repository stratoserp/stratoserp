<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class GoodsReceiptStorage extends SqlContentEntityStorage implements GoodsReceiptStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(GoodsReceiptInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_goods_receipt_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_goods_receipt_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(GoodsReceiptInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_goods_receipt_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_goods_receipt_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
