<?php

declare(strict_types=1);

namespace Drupal\se_xero\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\xero\XeroQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide configuration for xero integration.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Xero query.
   *
   * @var \Drupal\xero\XeroQuery
   */
  protected $xeroQuery;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   * @param \Drupal\xero\XeroQuery $xeroQuery
   *   Xero query instance.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, XeroQuery $xeroQuery, EntityTypeManager $entity_type_manager) {
    parent::__construct($config_factory);
    $this->xeroQuery = $xeroQuery;
    $this->entityTypeManager = $entity_type_manager;

    $this->setConfigFactory($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('xero.query'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'se_xero_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['se_xero.settings'];
  }

  /**
   * Retrieve the list of accounts from Xero.
   *
   * @return array
   *   The list of accounts.
   */
  public function retrieveAccountList(): array {
    $account_options = [];
    if (!$accounts = $this->xeroQuery->getCache('xero_account')) {
      return [];
    }

    foreach ($accounts as $account) {
      // Bank accounts do not have a code, exclude them.
      if ($account->get('Code')->getValue()) {
        $account_options[$account->get('Code')->getValue()] =
          (string) $this->t('@code - @name', [
            '@code' => $account->get('Code')->getValue(),
            '@name' => $account->get('Name')->getValue(),
          ]);
      }
    }

    return $account_options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('se_xero.settings');

    $account_options = $this->retrieveAccountList();

    $form['se_xero_enabled'] = [
      '#title' => $this->t('Xero sync enabled'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('system.enabled'),
    ];

    $form['se_xero_invoice_account'] = [
      '#title' => $this->t('Select account number for invoices'),
      '#type' => 'select',
      '#options' => $account_options,
      '#default_value' => $config->get('invoice.account'),
    ];

    $form['se_xero_sync_start'] = [
      '#title' => $this->t('Sync start date'),
      '#type' => 'date',
      '#default_value' => $config->get('invoice.sync_start_date'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('se_xero.settings');
    $form_state_values = $form_state->getValues();
    $config
      ->set('system.enabled', $form_state_values['se_xero_enabled'])
      ->set('invoice.account', $form_state_values['se_xero_invoice_account'])
      ->set('invoice.sync_start_date', $form_state_values['se_xero_sync_start']);
    $config->save();
  }

}
