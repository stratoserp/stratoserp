<?php

declare(strict_types=1);

namespace Drupal\se_relationship\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Relationship edit forms.
 *
 * @ingroup se_relationship
 */
class RelationshipForm extends StratosContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

}
