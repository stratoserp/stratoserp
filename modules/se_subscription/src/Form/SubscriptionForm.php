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

    if ($business = \Drupal::service('se.form_alter')->getBusiness()) {
      $form['name']['widget'][0]['value']['#default_value'] =
        $this->t('@business - @type', [
          '@business' => $business->getName(),
          '@type' => $this->entity->type->entity->label(),
        ]);
    }

    return $form;
  }

}
