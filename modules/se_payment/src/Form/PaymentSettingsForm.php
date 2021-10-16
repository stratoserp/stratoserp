<?php

declare(strict_types=1);

namespace Drupal\se_payment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implementation of the Payment settings form.
 *
 * @ingroup se_payment
 */
class PaymentSettingsForm extends FormBase {

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
    return 'payment_settings';
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
    $config = \Drupal::configFactory()->getEditable('se_payment.settings');

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    $config->set('default_payment_vocabulary', $values['default_payment_vocabulary']);
    $config->set('default_payment_term', $values['default_payment_term']);
    $config->save();

    $this->messenger()->addMessage(t('Payment settings updated'));
  }

  /**
   * Defines the settings form for Payment entities.
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
    $form['payment_settings']['#markup'] = 'Settings form for Payment entities. Manage field settings here.';

    $config = $this->config('se_payment.settings');
    $termOptions = $vocabOptions = [];

    // Retrieve the field and then the vocab.
    $config = \Drupal::service('config.factory')->get('se_payment.settings');
    $vocabulary = $config->get('default_payment_vocabulary');

    // Build a list of vocbulary options.
    $vocabs = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->loadByProperties([]);
    foreach ($vocabs as $vid => $vocab) {
      $vocabOptions[$vid] = $vocab->label();
    }

    $form['default_payment_vocabulary'] = [
      '#title' => $this->t('Select payment vocabulary.'),
      '#type' => 'select',
      '#options' => $vocabOptions,
      '#default_value' => $config->get('default_payment_vocabulary'),
      '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
    ];

    if (!empty($vocabulary)) {
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => $vocabulary]);

      /**
       * @var \Drupal\taxonomy\Entity\Term $term
       */
      foreach ($terms as $tid => $term) {
        $termOptions[$tid] = $term->getName();
      }

      $form['default_payment_term'] = [
        '#title' => $this->t('Select payment default term (default type).'),
        '#type' => 'select',
        '#options' => $termOptions,
        '#default_value' => $config->get('default_payment_term'),
        '#description' => t("The vocabulary can be changed in the field configuration for 'Type'"),
      ];
    }
    else {
      // Provide a dummy form value.
      $form['default_payment_term'] = [
        '#type' => 'value',
        '#value' => FALSE,
      ];
      $this->messenger()->addWarning(t('Choose a vocabulary to get a list of terms.'));
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

}
