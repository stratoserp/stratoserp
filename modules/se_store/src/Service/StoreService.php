<?php

declare(strict_types=1);

namespace Drupal\se_store\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Store service class for common store related manipulation.
 */
class StoreService {

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
   * StoreService constructor.
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
   * Given a customer entity, return the main store for that customer.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to return the store for.
   *
   * @return array
   *   The list of stores set as main stores.
   */
  public function loadMainStoresByCustomer(EntityInterface $entity): array {
    // Ensure its really a customer passed.
    $customer = \Drupal::service('se_customer.service')->lookupCustomer($entity);

    if (!$customer) {
      return [];
    }

    // If no main store term is selected, bail.
    $config = $this->configFactory->get('se_store.settings');
    if (!$termId = $config->get('main_store_term')) {
      return [];
    }

    // Setup the query.
    $query = \Drupal::entityQuery('se_store')
      ->condition('se_cu_ref', $customer->id())
      ->condition('se_type_ref', $termId);

    // Return the executed query.
    return $query->execute();
  }

  /**
   * Given a customer entity, return all stores for the customer.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to return the stores for.
   *
   * @return array
   *   The list of stores.
   */
  public function loadStoresByCustomer(EntityInterface $entity): array {
    // Ensure it's really a customer entity.
    $customer = \Drupal::service('se_customer.service')->lookupCustomer($entity);

    return \Drupal::entityQuery('se_store')
      ->condition('se_cu_ref', $customer->id())
      ->execute();
  }

}
