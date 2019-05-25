<?php

namespace Drupal\se_core\Plugin\ExtraField\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\extra_field\Plugin\ExtraFieldFormBase;

/**
 * Provide a field version of the actions on an editing form.
 *
 * @ExtraFieldForm(
 *   id = "actions_field",
 *   label = @Translation("Actions field"),
 *   bundles = {
 *     "node.*",
 *   }
 * )
 */
class ActionsField extends ExtraFieldFormBase {

  public function formView(array $form, FormStateInterface $form_state) {
    return $form['actions'];
  }
}