<?php

namespace Drupal\se_invoice\Form;

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
    return 'se_invoice_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['se_invoice.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('se_invoice.settings');
    $vocab_options = [];
    $term_options = [];

    $vocabulary_storage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
    $vocabularies = $vocabulary_storage->loadByProperties();

    /**
     * @var \Drupal\taxonomy\Entity\Vocabulary $vocab
     */
    foreach ($vocabularies as $vid => $vocab) {
      $vocab_options[$vid] = $vocab->get('name');
    }

    $form['se_invoice_status_vocabulary'] = [
      '#title' => $this->t('Select invoice status vocabulary.'),
      '#type' => 'select',
      '#options' => $vocab_options,
      '#default_value' => $config->get('vocabulary'),
    ];

    $vocabulary = $config->get('vocabulary');
    if (isset($vocabulary)) {
      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $terms = $term_storage->loadByProperties(['vid' => $vocabulary]);

      /**
       * @var \Drupal\taxonomy\Entity\Term $term
       */
      foreach ($terms as $tid => $term) {
        $term_options[$tid] = $term->getName();
      }

      $form['se_invoice_invoice_status_term'] = [
        '#title' => $this->t('Select default invoice status.'),
        '#type' => 'select',
        '#options' => $term_options,
        '#default_value' => $config->get('invoice_status_term'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('se_invoice.settings');
    $form_state_values = $form_state->getValues();
    $config->set('vocabulary', $form_state_values['se_invoice_status_vocabulary']);
    if (isset($form_state_values['se_invoice_invoice_status_term'])) {
      $config->set('invoice_status_term', $form_state_values['se_invoice_invoice_status_term']);
    }
    $config->save();
  }

}
