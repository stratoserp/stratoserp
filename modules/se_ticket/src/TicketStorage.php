<?php

declare(strict_types=1);

namespace Drupal\se_ticket;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_ticket\Entity\TicketInterface;

/**
 * Defines the storage handler class for Ticket entities.
 *
 * This extends the base storage class, adding required special handling for
 * Ticket entities.
 *
 * @ingroup se_ticket
 */
class TicketStorage extends SqlContentEntityStorage implements TicketStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(TicketInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {se_ticket_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {se_ticket_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(TicketInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {se_ticket_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('se_ticket_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
