<?php

namespace Drupal\se_supplier;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class SupplierStorage extends SqlContentEntityStorage implements SupplierStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(SupplierInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_supplier_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_supplier_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(SupplierInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_supplier_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_supplier_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
