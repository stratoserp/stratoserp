<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Service\SetupStatusInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allow show/hide some parts of the custom navigation block.
 *
 * @todo Should this be configurable at the per user level instead?
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\stratoserp\Service\SetupStatusInterface
   */
  protected SetupStatusInterface $setupStatus;

  /**
   * Extend the parent create function to add services.
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->setupStatus = $container->get('se.setup_status');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['stratoserp.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stratoserp_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $settings = $this->config('stratoserp.settings');

    $setupComplete = $this->setupStatus->checkSetupStatus();
    $this->setupStatus->setupStatusError();

    $form['verify_setup_complete'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Verify that setup is complete on form display.'),
      '#default_value' => $settings->get('verify_setup_complete') ?: TRUE,
      '#attributes' => ['disabled' => !$setupComplete],
      '#description' => t('If this setting is not editable, there is are errors that need to be addressed. Once this field is editable, its safe to disable it for a tiny performance improvement.'),
    ];

    $form['first_contact'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add the first contact by default.'),
      '#default_value' => $settings->get('first_contact'),
    ];

    $form['hide_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide the Search box on the Navigation Block.'),
      '#default_value' => $settings->get('hide_search'),
    ];

    $form['hide_buttons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide the Contextual buttons on the Navigation Block.'),
      '#description' => $this->t('If local tasks are preferred to the block, this will hide the duplicate links in the Navigation Block.'),
      '#default_value' => $settings->get('hide_buttons'),
    ];

    $form['statistics_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable statistics display'),
      '#default_value' => $settings->get('statistics_display'),
    ];

    $form['statistics_timeframe'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the default timeframe for statistics graphs.'),
      '#options' => [
        1 => t('1 Year'),
        2 => t('2 Years'),
        3 => t('3 Years'),
        4 => t('4 Years'),
        5 => t('5 Years'),
      ],
      '#default_value' => $settings->get('statistics_timeframe'),
      '#states' => [
        'visible' => [
          ':input[name="statistics_display"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $form['statistics_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the default interval for statistics graphs.'),
      '#options' => [
        'weekly' => t('Weekly'),
        'monthly' => t('Monthly'),
      ],
      '#default_value' => $settings->get('statistics_style'),
      '#states' => [
        'visible' => [
          ':input[name="statistics_display"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('stratoserp.settings')
      ->set('verify_setup_complete', $form_state->getValue('verify_setup_complete'))
      ->set('first_contact', $form_state->getValue('first_contact'))
      ->set('hide_search', $form_state->getValue('hide_search'))
      ->set('hide_buttons', $form_state->getValue('hide_buttons'))
      ->set('statistics_display', $form_state->getValue('statistics_display'))
      ->set('statistics_timeframe', $form_state->getValue('statistics_timeframe'))
      ->set('statistics_style', $form_state->getValue('statistics_style'))
      ->save();
    parent::submitForm($form, $form_state);

    // Check/Update status on each save.
    $this->setupStatus->checkSetupStatus();

    Cache::invalidateTags(['stratoserp_navigation_block']);
  }

}
