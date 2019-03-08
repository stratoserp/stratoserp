<?php

namespace Drupal\se_contact\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class ContactService {

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
   * Given a customer node, return the main contact for that customer.
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return array|bool|int
   */
  public function loadMainContactByCustomer(Node $node) {
    $config = $this->configFactory->get('se_contact.settings');

    // If no main contact term is selected, bail.
    if (!$term_id = $config->get('main_contact_term')) {
      return [];
    }

    /** @var Term $term */
    $term = Term::load($term_id);

    $contacts = \Drupal::entityQuery('node')
      ->condition('type', 'se_contact')
      ->condition('field_bu_ref', $node->id())
      ->condition('field_co_type_ref', $term->id())
      ->execute();

    return $contacts;
  }
}
