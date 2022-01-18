<?php

declare(strict_types=1);

namespace Drupal\se_business\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "se_business",
 *   label = @Translation("Business: Filter by business type"),
 *   group = "se_business",
 *   weight = 1
 * )
 */
class EntityBusinessSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $termOptions = [];

    // Retrieve the field and then the vocab.
    $vocabulary = '';
    if ($field = $this->entityTypeManager->getStorage('field_config')->load('se_business.se_business.se_type_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);

      $terms = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => $vocabulary]);

      /**
       * @var \Drupal\taxonomy\Entity\Term $term
       */
      foreach ($terms as $tid => $term) {
        $termOptions[$tid] = $term->getName();
      }

      $form['business_type'] = [
        '#title' => $this->t('Select business type.'),
        '#type' => 'select',
        '#options' => $termOptions,
        '#default_value' => $this->configuration['business_type'],
        '#description' => t("The vocabulary can be changed in the field configuration for 'Business Type'"),
      ];
    }
    else {
      $this->messenger->addWarning('Business type selection requires a vocabulary to be selected.');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $configuration = $this->getConfiguration();
    $target_type = $configuration['target_type'];

    $query = $this->buildEntityQuery($match, $match_operator);
    if (isset($configuration['business_type'])) {
      $query->condition('se_type_ref', $configuration['business_type']);
    }

    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityTypeManager->getStorage($target_type)->loadMultiple($result);
    foreach ($entities as $entity_id => $entity) {
      $bundle = $entity->bundle();
      $options[$bundle][$entity_id] = Html::escape($this->entityRepository->getTranslationFromContext($entity)->label() ?? '');
    }

    return $options;
  }

}
