<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Subscription edit forms.
 *
 * @ingroup se_subscription
 */
class SubscriptionForm extends StratosContentEntityForm {

  /**
   * Auto generate a name for the subscription.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Based on the 'use customer invoice date' checkbox, show/hide the date.
    $form['se_next_due']['#states'] = [
      'invisible' => [
        ':input[name="se_use_bu_due[value]"]' => [
          'checked' => TRUE,
        ],
      ],
    ];

    return $form;
  }

}
