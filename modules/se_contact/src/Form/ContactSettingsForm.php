<?php

namespace Drupal\se_contact\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implementation of the Contact settings form.
 *
 * @ingroup se_contact
 */
class ContactSettingsForm extends FormBase {

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
    return 'contact_settings';
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
    $config = \Drupal::configFactory()->getEditable('se_contact.settings');

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    // Update the value if its changed.
    if (isset($values['main_contact_term'])
    && ($values['main_contact_term'] !== $config->get('main_contact_term'))) {
      if (!$term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['main_contact_term'])) {
        $this->messenger()->addError('Invalid term for contact, unable to update.');
        return;
      }

      $config->set('main_contact_term', $values['main_contact_term']);
      $config->save();

      $this->messenger()->addMessage(t('Main contact updated to %contact_type', ['%contact_type' => $term->label()]));
    }
  }

  /**
   * Defines the settings form for Contact entities.
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
    $form['contact_settings']['#markup'] = 'Default settings form for Contact entities. Manage field settings here.';

    $config = $this->config('se_contact.settings');
    $termOptions = [];

    // Retrieve the field and then the vocab.
    $field = $this->entityTypeManager->getStorage('field_config')->load('se_contact.se_contact.se_co_type_ref');
    $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);

    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vocabulary]);

    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $tid => $term) {
      $termOptions[$tid] = $term->getName();
    }

    $form['main_contact_term'] = [
      '#title' => $this->t('Select main contact identifying term.'),
      '#type' => 'select',
      '#options' => $termOptions,
      '#default_value' => $config->get('main_contact_term'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
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
