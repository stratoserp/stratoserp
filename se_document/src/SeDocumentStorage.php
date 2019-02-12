<?php

namespace Drupal\se_document;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class SeDocumentStorage extends SqlContentEntityStorage implements SeDocumentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(SeDocumentInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_document_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_document_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(SeDocumentInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_document_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_document_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
