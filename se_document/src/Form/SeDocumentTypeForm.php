<?php

namespace Drupal\se_document\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SeDocumentTypeForm.
 */
class SeDocumentTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $se_document_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $se_document_type->label(),
      '#description' => $this->t("Label for the Document type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $se_document_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\se_document\Entity\SeDocumentType::load',
      ],
      '#disabled' => !$se_document_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $se_document_type = $this->entity;
    $status = $se_document_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Document type.', [
          '%label' => $se_document_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Document type.', [
          '%label' => $se_document_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($se_document_type->toUrl('collection'));
  }

}
