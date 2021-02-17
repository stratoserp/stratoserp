<?php

declare(strict_types=1);

namespace Drupal\se_business;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_business\Entity\BusinessInterface;

/**
 * Defines the storage handler class for Business entities.
 *
 * This extends the base storage class, adding required special handling for
 * Business entities.
 *
 * @ingroup se_business
 */
class BusinessStorage extends SqlContentEntityStorage implements BusinessStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BusinessInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_business_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_business_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BusinessInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_business_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_business_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
