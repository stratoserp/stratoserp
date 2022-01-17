<?php

declare(strict_types=1);

namespace Drupal\stratoserp;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the storage handler class for Stratos entities.
 *
 * This extends the base storage class, adding required special handling for
 * Stratos entities.
 *
 * @todo The way the tables are done feels wrong, do better.
 *
 * @ingroup stratoserp
 */
class StratosStorage extends SqlContentEntityStorage implements StratosStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {' . $this->getRevisionTable() . '} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {' . $this->getRevisionDataTable() . '} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EntityInterface $entity) {
    return $this->database->query(
      'SELECT COUNT(*) FROM {' . $this->getRevisionTable() . '} WHERE id = :id AND default_langcode = 1',
      [':id' => $entity->id()]
    )->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update($this->getRevisionTable())
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
