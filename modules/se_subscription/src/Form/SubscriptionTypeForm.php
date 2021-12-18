<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for adding/editing Subscription types.
 */
class SubscriptionTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $se_subscription_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $se_subscription_type->label(),
      '#description' => $this->t("Label for the Subscription type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $se_subscription_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\se_subscription\Entity\SubscriptionType::load',
      ],
      '#disabled' => !$se_subscription_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $se_subscription_type = $this->entity;
    $status = $se_subscription_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Subscription type.', [
          '%label' => $se_subscription_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Subscription type.', [
          '%label' => $se_subscription_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($se_subscription_type->toUrl('collection'));
  }

}
