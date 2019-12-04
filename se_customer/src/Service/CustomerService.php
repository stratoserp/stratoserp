<?php

declare(strict_types=1);

namespace Drupal\se_customer\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;

/**
 * Customer service class for common custom related manipulations.
 */
class CustomerService {

  /**
   * The config factory.
   *
   * @var configFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * SeContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Provide a config factory to the constructor.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Provide an entityTypeManager to the constructor.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Given any node with a customer, return the customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Node to return the customer for.
   *
   * @return bool|\Drupal\node\Entity\Node
   *   Customer node.
   */
  public function lookupCustomer(Node $node) {
    if ($node->bundle() === 'se_customer') {
      return $node;
    }

    if ($customers = $node->field_bu_ref->referencedEntities()) {
      return reset($customers);
    }

    return FALSE;
  }

  /**
   * Retrieve the current balance for a customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Customer to return the balance for.
   *
   * @return int
   *   The balance for the customer in cents.
   */
  public function getBalance(Node $node) {
    return (int)$node->field_cu_balance->value;
  }

  /**
   * Set the customers balance to a specific value.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Customer to set the balance for.
   * @param int $value
   *   Amount to set as the customer balance in cents.
   *
   * @return int
   *   The balance of the customers account afterwards.
   */
  public function setBalance(Node $node, int $value) {
    $node->field_cu_balance->value = $value;
    try {
      $node->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_customer')->error('Error updating customer balance, this is very bad.');
    }
    return $this->getBalance($node);
  }

  /**
   * Add a value to the customers existing balance.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Customer to adjust the balance for.
   * @param int $value
   *   The value to be added to the customer, positives and negatives will work.
   *
   * @return int
   *   The balance of the customers account afterwards.
   */
  public function adjustBalance(Node $node, int $value) {
    $node->field_cu_balance->value += $value;
    try {
      $node->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_customer')->error('Error updating customer balance, this is very bad.');
    }
    return $this->getBalance($node);
  }

}
