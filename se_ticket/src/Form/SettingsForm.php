<?php

namespace Drupal\se_ticket\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var EntityTypeManager $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;

    $this->setConfigFactory($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'se_ticket_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['se_ticket.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('se_ticket.settings');
    $vocab_options = [];

    $vocabulary_storage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
    $vocabularies = $vocabulary_storage->loadByProperties();

    /**
     * @var \Drupal\taxonomy\Entity\Vocabulary $vocab
     */
    foreach ($vocabularies as $vid => $vocab) {
      $vocab_options[$vid] = $vocab->get('name');
    }

    $form_parts = [
      'se_ticket_status' => [
        'vocab' => [
          'title' => $this->t('Select ticket status vocabulary.'),
          'field' => 'se_ticket_status_vocabulary',
          'config' => 'status_vocabulary',
        ],
        'term' => [
          'title' => $this->t('Select default ticket status.'),
          'field' => 'se_ticket_status_ticket_term',
          'config' => 'status_default_term',
        ],
      ],
      'se_ticket_priority' => [
        'vocab' => [
          'title' => $this->t('Select ticket priority vocabulary.'),
          'field' => 'se_ticket_priority_vocabulary',
          'config' => 'priority_vocabulary',
        ],
        'term' => [
          'title' => $this->t('Select default ticket priority.'),
          'field' => 'se_ticket_priority_ticket_term',
          'config' => 'priority_default_term',
        ],
      ],
      'se_ticket_type' => [
        'vocab' => [
          'title' => $this->t('Select ticket type vocabulary.'),
          'field' => 'se_ticket_type_vocabulary',
          'config' => 'type_vocabulary',
        ],
        'term' => [
          'title' => $this->t('Select default ticket type.'),
          'field' => 'se_ticket_type_ticket_term',
          'config' => 'type_default_term',
        ],
      ],
    ];

    foreach ($form_parts as $fields) {
      $form[$fields['vocab']['field']] = [
        '#title' => $fields['vocab']['title'],
        '#type' => 'select',
        '#options' => $vocab_options,
        '#default_value' => $config->get($fields['vocab']['config']),
      ];

      $vocabulary = $config->get($fields['vocab']['config']);
      if (isset($vocabulary)) {
        $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
        $terms = $term_storage->loadByProperties(['vid' => $vocabulary]);

        /**
         * @var \Drupal\taxonomy\Entity\Term $term
         */
        $term_options = [];
        foreach ($terms as $tid => $term) {
          $term_options[$tid] = $term->getName();
        }

        $form[$fields['term']['field']] = [
          '#title' => $fields['term']['title'],
          '#type' => 'select',
          '#options' => $term_options,
          '#default_value' => $config->get($fields['term']['config']),
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('se_ticket.settings');
    $form_state_values = $form_state->getValues();
    $config
      ->set('status_vocabulary', $form_state_values['se_ticket_status_vocabulary'])
      ->set('priority_vocabulary', $form_state_values['se_ticket_priority_vocabulary'])
      ->set('type_vocabulary', $form_state_values['se_ticket_type_vocabulary']);

    if (isset($form_state_values['se_ticket_status_ticket_term'])) {
      $config->set('status_default_term', $form_state_values['se_ticket_status_ticket_term']);
    }
    if (isset($form_state_values['se_ticket_priority_ticket_term'])) {
      $config->set('priority_default_term', $form_state_values['se_ticket_priority_ticket_term']);
    }
    if (isset($form_state_values['se_ticket_type_ticket_term'])) {
      $config->set('type_default_term', $form_state_values['se_ticket_type_ticket_term']);
    }
    $config->save();
  }
}
