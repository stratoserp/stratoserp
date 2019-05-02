<?php

namespace Drupal\se_information\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class InformationTypeForm.
 */
class InformationTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $se_information_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $se_information_type->label(),
      '#description' => $this->t("Label for the Information type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $se_information_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\se_information\Entity\InformationType::load',
      ],
      '#disabled' => !$se_information_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $se_information_type = $this->entity;
    $status = $se_information_type->save();

    $messenger = \Drupal::messenger();

    switch ($status) {
      case SAVED_NEW:
        $messenger->addMessage($this->t('Created the %label Information type.', [
          '%label' => $se_information_type->label(),
        ]));
        break;

      default:
        $messenger->addMessage($this->t('Saved the %label Information type.', [
          '%label' => $se_information_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($se_information_type->toUrl('collection'));
  }

}
