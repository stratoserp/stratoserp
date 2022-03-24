<?php

declare(strict_types=1);

namespace Drupal\se_information\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Information edit forms.
 *
 * @ingroup se_information
 */
class InformationForm extends StratosContentEntityForm {

  /**
   * Auto generate a name for the information.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if ($customer = \Drupal::service('se.form_alter')->getCustomer()) {
      $form['name']['widget'][0]['value']['#default_value'] =
        $this->t('@customer - @type', [
          '@customer' => $customer->getName(),
          '@type' => $this->entity->type->entity->label(),
        ]);
    }

    return $form;
  }

}
