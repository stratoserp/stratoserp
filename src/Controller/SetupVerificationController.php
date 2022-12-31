<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SetupVerificationController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Simple constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    $entityTypeManager = $container->get('entity_type.manager');

    return new static($entityTypeManager);
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'setup_verification';
  }

  public function verification() {

    $terms = [];
    $build = [];

    $config = $this->config('se_ticket.settings');
    $fieldStorage = $this->entityTypeManager->getStorage('field_config');
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Retrieve the field and then the vocab.
    if ($field = $fieldStorage->load('se_ticket.se_ticket.se_priority_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);

      // If the vocabulary isn't set...
      if (!isset($vocabulary)) {
        $build['priority_vocab'] = Url::fromRoute('entity.field_config.se_ticket_field_edit_form', [
          'field_config' => 'se_ticket.se_ticket.se_priority_ref',
        ]);
      }
      else {
        $terms = $termStorage->loadByProperties(['vid' => $vocabulary]);
      }

      // If there are no terms...
      if (!count($terms)) {
        $build['priority_terms'] = Url::fromRoute('entity.taxonomy_vocabulary.overview_form', [
          'taxonomy_vocabulary' => $vocabulary,
        ]);
      }

      // If the default priority isn't set...
      if (!$config->get('se_ticket_priority')) {
        $build['priority_default'] = Url::fromRoute('se_ticket.settings', []);
      }
    }

    // Retrieve the field and then the vocab.
    if ($field = $fieldStorage->load('se_ticket.se_ticket.se_type_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
      $terms = $termStorage->loadByProperties(['vid' => $vocabulary]);
    }

    return $build;

  }

}
