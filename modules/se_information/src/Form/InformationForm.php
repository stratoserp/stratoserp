<?php

declare(strict_types=1);

namespace Drupal\se_information\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;

/**
 * Form controller for Information edit forms.
 *
 * @ingroup se_information
 */
class InformationForm extends ContentEntityForm {

  use RevisionableEntityTrait;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $account;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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

}
