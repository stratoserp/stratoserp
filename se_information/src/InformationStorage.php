<?php

namespace Drupal\se_information;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_information\Entity\InformationInterface;

/**
 * Defines the storage handler class for Information entities.
 *
 * This extends the base storage class, adding required special handling for
 * Information entities.
 *
 * @ingroup se_information
 */
class InformationStorage extends SqlContentEntityStorage implements InformationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(InformationInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_information_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_information_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(InformationInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_information_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_information_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
