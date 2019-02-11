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

    $item_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $item_type->label(),
      '#description' => $this->t("Label for the Item type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $item_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\se_item\Entity\ItemType::load',
      ],
      '#disabled' => !$item_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $item_type = $this->entity;
    $status = $item_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Item type.', [
          '%label' => $item_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Item type.', [
          '%label' => $item_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($item_type->toUrl('collection'));
  }

}
