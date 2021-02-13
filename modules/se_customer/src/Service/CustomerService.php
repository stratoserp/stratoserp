<?php

declare(strict_types=1);

namespace Drupal\se_customer\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_customer\Entity\Customer;
use Drupal\se_customer\Entity\CustomerInterface;

/**
 * Customer service class for common custom related manipulations.
 */
class CustomerService {

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
   * CustomerService constructor.
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
   * Given any entity with a customer, return the (first) customer.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to return the customer for.
   *
   * @return bool|\Drupal\se_customer\Entity\Customer
   *   Customer entity.
   */
  public function lookupCustomer(EntityInterface $entity) {
    if ($entity instanceof CustomerInterface) {
      return $entity;
    }

    if (isset($entity->se_bu_ref) && $customers = $entity->se_bu_ref->referencedEntities()) {
      return reset($customers);
    }

    return FALSE;
  }

  /**
   * Retrieve the current balance for a customer.
   *
   * @param \Drupal\se_customer\Entity\Customer $entity
   *   Customer to return the balance for.
   *
   * @return int
   *   The balance for the customer in cents.
   */
  public function getBalance(Customer $entity): int {
    return (int) $entity->se_cu_balance->value;
  }

  /**
   * Set the customers balance to a specific value.
   *
   * @param \Drupal\se_customer\Entity\Customer $entity
   *   Customer to set the balance for.
   * @param int $value
   *   Amount to set as the customer balance in cents.
   *
   * @return int
   *   The balance of the customers account afterwards.
   */
  public function setBalance(Customer $entity, int $value): int {
    $entity->se_cu_balance->value = $value;
    try {
      $entity->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_customer')->error('Error updating customer balance, this is very bad.');
    }
    return $this->getBalance($entity);
  }

  /**
   * Add a value to the customers existing balance.
   *
   * @param \Drupal\se_customer\Entity\Customer $entity
   *   Customer to adjust the balance for.
   * @param int $value
   *   The value to be added to the customer, positives and negatives will work.
   *
   * @return int
   *   The balance of the customers account afterwards.
   */
  public function adjustBalance(Customer $entity, int $value): int {
    $entity->se_cu_balance->value += $value;
    try {
      $entity->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_customer')->error('Error updating customer balance, this is very bad.');
    }
    return $this->getBalance($entity);
  }

}
