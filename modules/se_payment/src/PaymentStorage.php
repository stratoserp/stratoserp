<?php

declare(strict_types=1);

namespace Drupal\se_payment;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_payment\Entity\PaymentInterface;

/**
 * Defines the storage handler class for Payment entities.
 *
 * This extends the base storage class, adding required special handling for
 * Payment entities.
 *
 * @ingroup se_payment
 */
class PaymentStorage extends SqlContentEntityStorage implements PaymentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(PaymentInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_payment_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_payment_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(PaymentInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_payment_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_payment_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
