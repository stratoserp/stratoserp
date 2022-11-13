<?php

declare(strict_types=1);

namespace Drupal\se_contact\Service;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Interface for the contact service.
 */
interface ContactServiceInterface {

  /**
   * Given a customer entity, return the main contact for that customer.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   Entity to return the contact for.
   *
   * @return array
   *   The list of contacts set as main contacts.
   */
  public function loadMainContactsByCustomer(StratosEntityBaseInterface $entity): array;

  /**
   * Return the default contacts based on the entity type.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   Entity to return the contact for.
   *
   * @return array
   *   The default set contacts based on entity type.
   */
  public function loadDefaultContactsFromEntity(StratosEntityBaseInterface $entity): array;

  /**
   * Given an entity, return all contacts for the customer associated.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   Entity to return the contacts for.
   * @param int|null $contactType
   *   Filter by specific contact type.
   *
   * @return array
   *   The list of contacts.
   */
  public function loadContactsFromEntity(StratosEntityBaseInterface $entity, int $contactType = NULL): array;

  /**
   * Given an entity, return all email contacts for the customer associated.
   *
   * @param array $contactIdList
   *   Contact id's to convert to emails.
   *
   * @return array
   *   The list of contacts emails.
   */
  public function contactsToEmails(array $contactIdList): array;

}
