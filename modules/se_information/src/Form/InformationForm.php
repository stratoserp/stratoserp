<?php

declare(strict_types=1);

namespace Drupal\se_information\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Information edit forms.
 *
 * @ingroup se_information
 */
class InformationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\se_information\Entity\Information $entity */
    $form = parent::buildForm($form, $form_state);

    \Drupal::service('se.form_alter')->setBusinessField($form, 'se_bu_ref');

    if (!$this->entity->isNew()) {
      $form['group_in_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    $messenger = \Drupal::messenger();

    switch ($status) {
      case SAVED_NEW:
        $messenger->addMessage($this->t('Created the %label Information.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $messenger->addMessage($this->t('Saved the %label Information.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.se_information.canonical', ['se_information' => $entity->id()]);
  }

}
