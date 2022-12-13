<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workflows\Entity\Workflow;

/**
 * Implementation of the Ticket settings form.
 *
 * @ingroup se_ticket
 */
class TicketSettingsForm extends FormBase {

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
    return 'ticket_settings';
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
    $config = $this->configFactory()->getEditable('se_ticket.settings');
    $fieldStorage = $this->entityTypeManager->getStorage('field_config');
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $messenger = $this->messenger();

    // Retrieve the submitted values.
    $values = $form_state->getValues();

    // Update the value if its changed.
    if (isset($values['se_ticket_priority'])
      && ($values['se_ticket_priority'] !== $config->get('se_ticket_priority'))) {
      if (!$term = $termStorage->load($values['se_ticket_priority'])) {
        $messenger->addError('Invalid term for ticket priority, unable to update.');
        return;
      }

      // Now set the last one as the default for the field.
      if ($field = $fieldStorage->load('se_ticket.se_ticket.se_priority_ref')) {
        $field->setDefaultValue(['target_uuid' => $term->uuid()]);
        $field->save();

        $config->set('se_ticket_priority', $values['se_ticket_priority']);
        $config->save();
      }

      $messenger->addMessage(t('Ticket priority term updated to %priority_term', [
        '%priority_term' => $term->label()
      ]));
    }

    // Update the value if its changed.
    if (isset($values['se_ticket_type'])
      && ($values['se_ticket_type'] !== $config->get('se_ticket_type'))) {
      if (!$term = $termStorage->load($values['se_ticket_type'])) {
        $messenger->addError('Invalid term for ticket type, unable to update.');
        return;
      }

      // Now set the last one as the default for the field.
      if ($field = $fieldStorage->load('se_ticket.se_ticket.se_type_ref')) {
        $field->setDefaultValue(['target_uuid' => $term->uuid()]);
        $field->save();

        $config->set('se_ticket_type', $values['se_ticket_type']);
        $config->save();
      }

      $messenger->addMessage(t('Ticket type term updated to %type_term', [
        '%type_term' => $term->label()
      ]));
    }

    // Update config data if its changed.
    $typeTermLabels = [];
    if (isset($values['se_ticket_calendar_type_list'])
      && ($values['se_ticket_calendar_type_list'] !== $config->get('se_ticket_calendar_type_list'))) {

      // Validate the terms.
      foreach ($values['se_ticket_calendar_type_list'] as $tid) {
        if (!$term = $termStorage->load($tid)) {
          $messenger->addError('Invalid term for ticket status, unable to update.');
          return;
        }
        $typeTermLabels[] = $term->label();
      }

      $config->set('se_ticket_calendar_type_list', $values['se_ticket_calendar_type_list']);
      $config->save();

      if (count($typeTermLabels)) {
        $typeTermCombined = implode(', ', $typeTermLabels);
      }
      else {
        $typeTermCombined = t('None');
      }

      $messenger->addMessage(t('Ticket calendar type list updated to %type_terms', [
        '%type_terms' => $typeTermCombined
      ]));
    }

    // Update config data if its changed.
    if (isset($values['se_ticket_calendar_status_list'])) {

      $config->set('se_ticket_calendar_status_list', $values['se_ticket_calendar_status_list']);
      $config->save();

      $messenger->addMessage(t('Ticket calendar status list updated to %status_terms', [
        '%status_terms' => implode(',', $values['se_ticket_calendar_status_list'])
      ]));
    }

  }

  /**
   * Defines the settings form for Ticket entities.
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
    $form['ticket_settings']['#markup'] = 'Settings form for Ticket entities. Manage field settings here.';

    $config = $this->config('se_ticket.settings');
    $fieldStorage = $this->entityTypeManager->getStorage('field_config');
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $messenger = $this->messenger();
    $priorityOptions = $typeOptions = $statusOptions = [];

    // Retrieve the field and then the vocab.
    if ($field = $fieldStorage->load('se_ticket.se_ticket.se_priority_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
      $terms = $termStorage->loadByProperties(['vid' => $vocabulary]);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $tid => $term) {
        $priorityOptions[$tid] = $term->getName();
      }

      if (!count($priorityOptions)) {
        $messenger->addWarning(t('Ticket priority: No terms found in the %vocabulary vocabulary', [
          '%vocabulary' => $vocabulary
        ]));
      }

      $form['se_ticket_priority'] = [
        '#title' => $this->t('Select default ticket priority.'),
        '#type' => 'select',
        '#options' => $priorityOptions,
        '#default_value' => $config->get('se_ticket_priority'),
        '#description' => t("The vocabulary can be changed in the field configuration for 'Ticket priority'"),
      ];
    }

    // Retrieve the field and then the vocab.
    if ($field = $fieldStorage->load('se_ticket.se_ticket.se_type_ref')) {
      $vocabulary = reset($field->getSettings()['handler_settings']['target_bundles']);
      $terms = $termStorage->loadByProperties(['vid' => $vocabulary]);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $tid => $term) {
        $typeOptions[$tid] = $term->getName();
      }

      if (!count($typeOptions)) {
        $messenger->addWarning(t('Ticket type: No terms found in the %vocabulary vocabulary', [
          '%vocabulary' => $vocabulary
        ]));
      }

      $form['se_ticket_type'] = [
        '#title' => $this->t('Select default ticket type.'),
        '#type' => 'select',
        '#options' => $typeOptions,
        '#default_value' => $config->get('se_ticket_type'),
        '#description' => t("The vocabulary can be changed in the field configuration for 'Ticket type'"),
      ];

      $form['se_ticket_calendar_type_list'] = [
        '#title' => $this->t('Select valid ticket types for calender.'),
        '#type' => 'select',
        '#multiple' => TRUE,
        '#options' => $typeOptions,
        '#default_value' => $config->get('se_ticket_calendar_type_list'),
        '#description' => t("The vocabulary can be changed in the field configuration for 'Ticket type'"),
      ];
    }

    // Retrieve the workflow options.
    if (!$workflow = Workflow::load('se_ticket_workflow')) {
      $state_labels = [];
    }
    else {
      $state_labels = array_map(function ($state) {
        return $state->label();
      }, $workflow->getTypePlugin()->getStates());
    }

    $form['se_ticket_calendar_status_list'] = [
      '#title' => $this->t('Select valid ticket status for calendar.'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#options' => $state_labels,
      '#default_value' => $config->get('se_ticket_calendar_status_list'),
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
