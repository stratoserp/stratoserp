<?php

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 *
 */
class InvoiceService {

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
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @param \Drupal\node\Entity\Node $customer
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getOutstandingInvoices(Node $customer) {
    /** @var Term $open_term */
    $open_term = $this->getOpenTerm();
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'se_invoice');
    $query->condition('se_bu_ref', $customer->id());
    $query->condition('se_status_ref', $open_term->id());
    $entity_ids = $query->execute();

    $invoices = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadMultiple($entity_ids);

    return $invoices;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   */
  public function getPaidTerm() {
    $term = taxonomy_term_load_multiple_by_name('closed', 'se_status');
    return reset($term);
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   */
  public function getOpenTerm() {
    $term = taxonomy_term_load_multiple_by_name('open', 'se_status');
    return reset($term);
  }

}

