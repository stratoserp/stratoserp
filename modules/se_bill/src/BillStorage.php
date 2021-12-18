<?php

declare(strict_types=1);

namespace Drupal\se_bill;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_bill\Entity\BillInterface;

/**
 * Defines the storage handler class for Bill entities.
 *
 * This extends the base storage class, adding required special handling for
 * Bill entities.
 *
 * @ingroup se_bill
 */
class BillStorage extends SqlContentEntityStorage implements BillStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BillInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_bill_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_bill_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BillInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_bill_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_bill_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
