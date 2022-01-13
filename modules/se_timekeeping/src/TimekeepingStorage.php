<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_timekeeping\Entity\TimekeepingInterface;

/**
 * Defines the storage handler class for Timekeeping entities.
 *
 * This extends the base storage class, adding required special handling for
 * Timekeeping entities.
 *
 * @ingroup se_timekeeping
 */
class TimekeepingStorage extends SqlContentEntityStorage implements TimekeepingStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(TimekeepingInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_timekeeping_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_timekeeping_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(TimekeepingInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_timekeeping_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_timekeeping_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
