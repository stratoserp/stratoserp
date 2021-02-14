<?php

declare(strict_types=1);

namespace Drupal\se_invoice;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_invoice\Entity\InvoiceInterface;

/**
 * Defines the storage handler class for Invoice entities.
 *
 * This extends the base storage class, adding required special handling for
 * Invoice entities.
 *
 * @ingroup se_invoice
 */
class InvoiceStorage extends SqlContentEntityStorage implements InvoiceStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(InvoiceInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_invoice_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_invoice_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(InvoiceInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_invoice_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_invoice_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
