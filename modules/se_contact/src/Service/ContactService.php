<?php

declare(strict_types=1);

namespace Drupal\se_contact\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Contact service class for common contact related manipulation.
 */
class ContactService {

  /**
   * The config factory.
   *
   * @var configFactory
   */
  protected ConfigFactory $configFactory;

  /**
   * The entity type manager.
   *
   * @var entityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * ContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Provide a config factory to the constructor.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Provide an entityTypeManager to the constructor.
   */
  public function __construct(ConfigFactory $configFactory, EntityTypeManager $entityTypeManager) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Given a customer entity, return the main contact for that customer.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to return the contact for.
   *
   * @return array
   *   The list of contacts set as main contacts.
   */
  public function loadMainContactsByCustomer(EntityInterface $entity): array {
    // Ensure its really a customer passed.
    $customer = \Drupal::service('se_customer.service')->lookupCustomer($entity);

    if (!$customer) {
      return [];
    }

    // If no main contact term is selected, bail.
    $config = $this->configFactory->get('se_contact.settings');
    if (!$termId = $config->get('main_contact_term')) {
      return [];
    }

    // Setup the query.
    $query = \Drupal::entityQuery('se_contact')
      ->condition('se_cu_ref', $customer->id())
      ->condition('se_type_ref', $termId);

    // Return the executed query.
    return $query->execute();
  }

  /**
   * Given a customer entity, return all contacts for the customer.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to return the contacts for.
   *
   * @return array
   *   The list of contacts.
   */
  public function loadContactsByCustomer(EntityInterface $entity): array {
    // Ensure it's really a customer entity.
    $customer = \Drupal::service('se_customer.service')->lookupCustomer($entity);

    return \Drupal::entityQuery('se_contact')
      ->condition('se_cu_ref', $customer->id())
      ->execute();
  }

}
