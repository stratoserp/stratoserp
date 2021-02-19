<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_business\Entity\Business;

/**
 * Service for various invoice related functions.
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
   *   The config factory being used.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager being used.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Retrieve the outstnading invoices for a business.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The Business node.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The found entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getOutstandingInvoices(Business $business) {
    /** @var \Drupal\taxonomy\Entity\Term $open_term */
    $open_term = $this->getOpenTerm();
    $query = \Drupal::entityQuery('se_invoice');
    $query->condition('se_bu_ref', $business->id());
    $query->condition('se_status_ref', $open_term->id());
    $entity_ids = $query->execute();

    return \Drupal::entityTypeManager()
      ->getStorage('se_invoice')
      ->loadMultiple($entity_ids);
  }

  /**
   * Retrieve the term user for paid status.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term for paid status.
   */
  public function getPaidTerm() {
    $term = taxonomy_term_load_multiple_by_name('closed', 'se_status');
    return reset($term);
  }

  /**
   * Retrieve the term user for open status.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term for open status.
   */
  public function getOpenTerm() {
    $term = taxonomy_term_load_multiple_by_name('open', 'se_status');
    return reset($term);
  }

}
