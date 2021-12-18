<?php

declare(strict_types=1);

namespace Drupal\se_ticket;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface TicketStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Ticket revision IDs for a specific Ticket.
   *
   * @param \Drupal\se_ticket\Entity\TicketInterface $entity
   *   The Ticket entity.
   *
   * @return int[]
   *   Ticket revision IDs (in ascending order).
   */
  public function revisionIds(TicketInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Ticket author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Ticket revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_ticket\Entity\TicketInterface $entity
   *   The Ticket entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(TicketInterface $entity);

  /**
   * Unsets the language for all Ticket with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
