<?php

declare(strict_types=1);

namespace Drupal\se_contact\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  protected EntityTypeManager $entityTypeManager;

  /**
   * Simple constructor.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
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
    $form['contact_settings']['#markup'] = 'Settings form for Contact entities.';

    $config = $this->config('se_contact.settings');
    $termOptions = [];

    // Retrieve the field and then the vocab.
    $vocabulary = '';
    if ($field = $this->entityTypeManager
      ->getStorage('field_config')
      ->load('se_contact.se_contact.se_type_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
    }

    $terms = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => $vocabulary,
      ]);

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
      '#description' => t("This is used as the default for emailing Quotes."),
    ];

    $form['accounting_contact_term'] = [
      '#title' => $this->t('Select accounting contact identifying term.'),
      '#type' => 'select',
      '#options' => $termOptions,
      '#default_value' => $config->get('accounting_contact_term'),
      '#description' => t("This is used as the default for emailing Invoices and Automatic statements."),
    ];

    $form['contact_vocabulary']['#markup'] =
      'If the options showing are not what you expect, the vocabulary can be changed in the
       field configuration for the <strong>Type</strong> entity reference field using
       <strong>Manage fields</strong>.';

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
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
    $config = $this->configFactory()->getEditable('se_contact.settings');

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    // Update the values.
    $config->set('main_contact_term', $values['main_contact_term']);
    $config->set('accounting_contact_term', $values['accounting_contact_term']);
    $config->save();

    $this->messenger()->addMessage(t('Contact terms updated'));
  }

}
