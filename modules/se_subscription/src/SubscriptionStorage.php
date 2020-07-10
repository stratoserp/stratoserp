<?php

namespace Drupal\se_subscription;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_subscription\Entity\SubscriptionInterface;

/**
 * Defines the storage handler class for Subscription entities.
 *
 * This extends the base storage class, adding required special handling for
 * Subscription entities.
 *
 * @ingroup se_subscription
 */
class SubscriptionStorage extends SqlContentEntityStorage implements SubscriptionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(SubscriptionInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_subscription_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_subscription_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(SubscriptionInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_subscription_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_subscription_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
