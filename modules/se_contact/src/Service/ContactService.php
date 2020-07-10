<?php

declare(strict_types=1);

namespace Drupal\se_contact\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Simple service the take
 */
class ContactService {

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
   * Given a customer node, return the main contact for that customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return array
   */
  public function loadMainContactByBusiness(Node $node): array {
    $config = $this->configFactory->get('se_contact.settings');

    // If no main contact term is selected, bail.
    if (!$term_id = $config->get('main_contact_term')) {
      return [];
    }

    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = Term::load($term_id);

    return \Drupal::entityQuery('node')
      ->condition('type', 'se_contact')
      ->condition('se_bu_ref', $node->id())
      ->condition('se_co_type_ref', $term->id())
      ->execute();
  }

}
