<?php

namespace Drupal\se_core\Plugin\ExtraField\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\extra_field\Plugin\ExtraFieldFormBase;

/**
 * Provide a field version of the status on an editing form.
 *
 * @ExtraFieldForm(
 *   id = "meta_field",
 *   label = @Translation("Meta field"),
 *   bundles = {
 *     "node.*",
 *   }
 * )
 */
class MetaField extends ExtraFieldFormBase {

  /**
   *
   */
  public function formView(array $form, FormStateInterface $form_state) {
    $meta_form = [];

    $fields = [
      'meta', 'author', 'status',
    ];

    $meta_form['meta_group'] = [
      '#type' => 'fieldset',
      '#open' => FALSE,
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#title' => $this->t('Meta information'),
    ];

    foreach ($fields as $field) {
      $meta_form['meta_group'][$field] = $form[$field];
      unset($meta_form['meta_group'][$field]['#group']);
    }

    return [
      '#markup' => render($meta_form),
    ];
  }

}
