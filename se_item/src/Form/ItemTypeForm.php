<?php

namespace Drupal\se_item\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ItemTypeForm.
 */
class ItemTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $se_item_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $se_item_type->label(),
      '#description' => $this->t("Label for the Item type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $se_item_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\se_item\Entity\ItemType::load',
      ],
      '#disabled' => !$se_item_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $se_item_type = $this->entity;
    $status = $se_item_type->save();

    $messenger = \Drupal::messenger();

    switch ($status) {
      case SAVED_NEW:

        $messenger->addMessage($this->t('Created the %label Item type.', [
          '%label' => $se_item_type->label(),
        ]));
        break;

      default:
        $messenger->addMessage($this->t('Saved the %label Item type.', [
          '%label' => $se_item_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($se_item_type->toUrl('collection'));
  }

}
