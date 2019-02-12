<?php

namespace Drupal\se_item;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_item\Entity\SeItemInterface;

/**
 * Defines the storage handler class for Item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Item entities.
 *
 * @ingroup se_item
 */
class SeItemStorage extends SqlContentEntityStorage implements SeItemStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(SeItemInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_item_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_item_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(SeItemInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_item_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_item_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
