<?php

namespace Drupal\se_customer\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class CustomerService {

  /**
   * The config factory.
   *
   * @var $configFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * SeContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Given any node with a customer, return the customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return bool|\Drupal\node\Entity\Node
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
   *
   * @return float
   */
  public function getBalance(Node $node) {
    return (float) $node->field_cu_balance->value;
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   * @param $value
   *
   * @return float
   */
  public function setBalance(Node $node, $value) {
    $node->field_cu_balance->value = $value;
    $node->save();
    return $this->getBalance($node);
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   * @param $value
   *
   * @return float
   */
  public function adjustBalance(Node $node, $value) {
    $node->field_cu_balance->value += $value;
    $node->save();
    return $this->getBalance($node);
  }
}
