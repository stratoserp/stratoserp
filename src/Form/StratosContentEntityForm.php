<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;

/**
 * Implement common functionality for Stratos content forms.
 */
class StratosContentEntityForm extends ContentEntityForm {

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

    $formAlter = \Drupal::service('se.form_alter');
    $formAlter->setBusinessField($form, 'se_bu_ref');
    $formAlter->setContactField($form, 'se_co_ref');
    $form['name']['widget'][0]['value']['#default_value'] = $formAlter->generateTitle();

    if (!$this->entity->isNew()) {
      $form['group_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    return $form;
  }

}
