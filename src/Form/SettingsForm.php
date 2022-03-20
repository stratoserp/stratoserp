<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Allow show/hide some parts of the custom navigation block.
 *
 * @todo Should this be configurable at the per user level instead?
 */
class SettingsForm extends ConfigFormBase {

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
    ];

    $form['statistics_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the default timeframe for statistics graphs.'),
      '#options' => [
        'weekly' => t('Weekly'),
        'monthly' => t('Monthly'),
      ],
      '#default_value' => $settings->get('statistics_style'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('stratoserp.settings')
      ->set('first_contact', $form_state->getValue('first_contact'))
      ->set('hide_search', $form_state->getValue('hide_search'))
      ->set('hide_buttons', $form_state->getValue('hide_buttons'))
      ->set('statistics_timeframe', $form_state->getValue('statistics_timeframe'))
      ->set('statistics_style', $form_state->getValue('statistics_style'))
      ->save();
    parent::submitForm($form, $form_state);

    Cache::invalidateTags(['stratoserp_navigation_block']);
  }

}
