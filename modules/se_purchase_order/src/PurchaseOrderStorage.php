<?php

namespace Drupal\se_purchase_order;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class PurchaseOrderStorage extends SqlContentEntityStorage implements PurchaseOrderStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(PurchaseOrderInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_purchase_order_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_purchase_order_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(PurchaseOrderInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_purchase_order_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_purchase_order_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
