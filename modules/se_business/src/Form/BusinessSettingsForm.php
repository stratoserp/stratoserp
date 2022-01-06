<?php

declare(strict_types=1);

namespace Drupal\se_business\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implementation of the Business settings form.
 *
 * @ingroup se_business
 */
class BusinessSettingsForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Simple constructor.
   */
  public function __construct() {
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'business_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Create an editable config.
    $config = \Drupal::configFactory()->getEditable('se_business.settings');

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    // Update the value if its changed.
    if (isset($values['default_business_type'])
      && ($values['default_business_type'] !== $config->get('default_business_type'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['default_business_type'])) {
        $this->messenger()->addError('Invalid term for business, unable to update.');
        return;
      }

      $config->set('default_business_type', $values['default_business_type']);
      $config->save();

      $this->messenger()->addMessage(t('Default business type updated to %business_type', ['%business_type' => $term->label()]));
    }
  }

  /**
   * Defines the settings form for Business entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['business_settings']['#markup'] = 'Settings form for Business entities. Manage field settings here.';

    $config = $this->config('se_business.settings');
    $termOptions = [];

    // Retrieve the field and then the vocab.
    $vocabulary = '';
    if ($field = $this->entityTypeManager->getStorage('field_config')->load('se_business.se_business.se_bu_type_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
    }

    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vocabulary]);

    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $tid => $term) {
      $termOptions[$tid] = $term->getName();
    }

    $form['default_business_type'] = [
      '#title' => $this->t('Select default business type.'),
      '#type' => 'select',
      '#options' => $termOptions,
      '#default_value' => $config->get('default_business_type'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Business Type'"),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

}
