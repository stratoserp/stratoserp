<?php

namespace Drupal\se_core\Plugin\ExtraField\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\extra_field\Plugin\ExtraFieldFormBase;

/**
 * Provide a field version of the revision on an editing form.
 *
 * @ExtraFieldForm(
 *   id = "revision_field",
 *   label = @Translation("Revision field"),
 *   bundles = {
 *     "node.*",
 *   }
 * )
 */
class RevisionField extends ExtraFieldFormBase {

  public function formView(array $form, FormStateInterface $form_state) {
    $revision_form = [];

    $fields = [
      'revision',
      'revision_information',
      'revision_log'
    ];

    foreach ($fields as $field) {
      $revision_form[$field] = $form[$field];
      unset($revision_form[$field]['#group']);
    }

    return $revision_form;
  }
}