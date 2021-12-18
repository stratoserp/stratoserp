<?php

declare(strict_types=1);

namespace Drupal\se_quote;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_quote\Entity\QuoteInterface;

/**
 * Defines the storage handler class for Quote entities.
 *
 * This extends the base storage class, adding required special handling for
 * Quote entities.
 *
 * @ingroup se_quote
 */
class QuoteStorage extends SqlContentEntityStorage implements QuoteStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(QuoteInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_quote_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_quote_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(QuoteInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_quote_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_quote_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
