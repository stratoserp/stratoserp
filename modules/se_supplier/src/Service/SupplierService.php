<?php

declare(strict_types=1);

namespace Drupal\se_supplier\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;

/**
 * Supplier service class for common custom related manipulations.
 */
class SupplierService {

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
   * SupplierService constructor.
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
   * Given any node with a Supplier, return the (first) supplier.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Node to return the supplier for.
   *
   * @return bool|\Drupal\node\Entity\Node
   *   supplier node.
   */
  public function lookupSupplier(Node $node) {
    if ($node->bundle() === 'se_supplier') {
      return $node;
    }

    if (isset($node->se_bu_ref) && $suppliers = $node->se_bu_ref->referencedEntities()) {
      return reset($suppliers);
    }

    return FALSE;
  }

  /**
   * Retrieve the current balance for a Supplier.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Supplier to return the balance for.
   *
   * @return int
   *   The balance for the supplier in cents.
   */
  public function getBalance(Node $node): int {
    return (int) $node->se_cu_balance->value;
  }

  /**
   * Set the Suppliers balance to a specific value.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Supplier to set the balance for.
   * @param int $value
   *   Amount to set as the supplier balance in cents.
   *
   * @return int
   *   The balance of the suppliers account afterwards.
   */
  public function setBalance(Node $node, int $value): int {
    $node->se_cu_balance->value = $value;
    try {
      $node->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_supplier')->error('Error updating supplier balance, this is very bad.');
    }
    return $this->getBalance($node);
  }

  /**
   * Add a value to the Suppliers existing balance.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Supplier to adjust the balance for.
   * @param int $value
   *   The value to be added to the supplier, positives and negatives will work.
   *
   * @return int
   *   The balance of the suppliers account afterwards.
   */
  public function adjustBalance(Node $node, int $value): int {
    $node->se_cu_balance->value += $value;
    try {
      $node->save();
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('se_supplier')->error('Error updating supplier balance, this is very bad.');
    }
    return $this->getBalance($node);
  }

}
