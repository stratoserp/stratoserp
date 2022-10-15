<?php

declare(strict_types=1);

namespace Drupal\se_payment\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Payment edit forms.
 *
 * @ingroup se_payment
 */
class PaymentForm extends StratosContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

//    $form['actions']['submit']['#disabled'] = TRUE;

    return $form;
  }

}
