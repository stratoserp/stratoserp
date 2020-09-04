<?php

declare(strict_types=1);

namespace Drupal\se_contact\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;

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
   * Given a customer node, return the main contact for that customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Node to return the contact for.
   *
   * @return array
   *   The list of contacts set as main contacts.
   */
  public function loadMainContactsByBusiness(Node $node): array {
    // Ensure its really a customer node.
    $customer = \Drupal::service('se_customer.service')->lookupCustomer($node);
    $supplier = \Drupal::service('se_supplier.service')->lookupSupplier($node);

    if (!($customer || $supplier)) {
      return [];
    }

    // If no main contact term is selected, bail.
    $config = $this->configFactory->get('se_contact.settings');
    if (!$termId = $config->get('main_contact_term')) {
      return [];
    }

    // Setup the query.
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'se_contact');

    // Setup the or condition and add in the parameters.
    $businessTypes = $query->orConditionGroup();
    if ($customer) {
      $businessTypes->condition('se_bu_ref', $customer->id());
    }
    if ($supplier) {
      $businessTypes->condition('se_bu_ref', $supplier->id());
    }

    // Return the executed query.
    return $query
      ->condition($businessTypes)
      ->condition('se_co_type_ref', $termId)
      ->execute();
  }

  /**
   * Given a customer node, return all contacts for the customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Node to return the contacs for.
   *
   * @return array
   *   The list of contacts.
   */
  public function loadContactsByCustomer(Node $node): array {
    // Ensure its really a customer node.
    $customer = \Drupal::service('se_customer.service')->lookupCustomer($node);

    return \Drupal::entityQuery('node')
      ->condition('type', 'se_contact')
      ->condition('se_bu_ref', $customer->id())
      ->execute();
  }

}
