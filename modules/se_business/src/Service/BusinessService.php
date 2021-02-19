<?php

declare(strict_types=1);

namespace Drupal\se_business\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_business\Entity\Business;
use Drupal\se_business\Entity\BusinessInterface;

/**
 * Business service class for common custom related manipulations.
 */
class BusinessService {

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
   * BusinessService constructor.
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
   * Given any entity with a business, return the (first) business.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to return the business for.
   *
   * @return bool|\Drupal\se_business\Entity\Business
   *   Business entity.
   */
  public function lookupBusiness(EntityInterface $entity) {
    /** @var \Drupal\se_business\Entity\Business $entity */
    if ($entity instanceof BusinessInterface) {
      return $entity;
    }

    if (isset($entity->se_bu_ref) && $businesss = $entity->se_bu_ref->referencedEntities()) {
      return reset($businesss);
    }

    return FALSE;
  }

  /**
   * Retrieve the current balance for a business.
   *
   * @param \Drupal\se_business\Entity\Business $entity
   *   Business to return the balance for.
   *
   * @return int
   *   The balance for the business in cents.
   */
  public function getBalance(Business $entity): int {
    return (int) $entity->se_bu_balance->value;
  }

  /**
   * Set the businesss balance to a specific value.
   *
   * @param \Drupal\se_business\Entity\Business $entity
   *   Business to set the balance for.
   * @param int $value
   *   Amount to set as the business balance in cents.
   *
   * @return int
   *   The balance of the businesss account afterwards.
   */
  public function setBalance(Business $entity, int $value): int {
    $entity->se_bu_balance->value = $value;
    try {
      $entity->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_business')->error('Error updating business balance, this is very bad.');
    }
    return $this->getBalance($entity);
  }

  /**
   * Add a value to the businesss existing balance.
   *
   * @param \Drupal\se_business\Entity\Business $entity
   *   Business to adjust the balance for.
   * @param int $value
   *   The value to be added to the business, positives and negatives will work.
   *
   * @return int
   *   The balance of the businesss account afterwards.
   */
  public function adjustBalance(Business $entity, int $value): int {
    $entity->se_bu_balance->value += $value;
    try {
      $entity->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_business')->error('Error updating business balance, this is very bad.');
    }
    return $this->getBalance($entity);
  }

}
