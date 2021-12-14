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

  protected function getEditableConfigNames() {
    return ['stratoserp.settings'];
  }

  public function getFormId() {
    return 'stratoserp_configuration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['hide_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide the Search box on the Navigation Block.'),
      '#default_value' => $this->config('stratoserp.settings')->get('hide_search'),
    ];

    $form['hide_buttons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide the Contextual buttons on the Navigation Block.'),
      '#description' => $this->t('If local tasks are preferred to the block, this will hide the duplicate links in the Navigation Block.'),
      '#default_value' => $this->config('stratoserp.settings')->get('hide_buttons'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('stratoserp.settings')
      ->set('hide_search', $form_state->getValue('hide_search'))
      ->set('hide_buttons', $form_state->getValue('hide_buttons'))
      ->save();
    parent::submitForm($form, $form_state);

    Cache::invalidateTags(['stratoserp_navigation_block']);
  }

}
