<?php

declare(strict_types=1);

namespace Drupal\se_contact;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_contact\Entity\ContactInterface;

/**
 * Defines the storage handler class for Contact entities.
 *
 * This extends the base storage class, adding required special handling for
 * Contact entities.
 *
 * @ingroup se_contact
 */
class ContactStorage extends SqlContentEntityStorage implements ContactStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ContactInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_contact_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_contact_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ContactInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_contact_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_contact_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
