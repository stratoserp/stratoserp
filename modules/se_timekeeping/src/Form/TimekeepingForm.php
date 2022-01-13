<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\se_business\Entity\Business;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;

/**
 * Form controller for Timekeeping edit forms.
 *
 * @ingroup se_timekeeping
 */
class TimekeepingForm extends ContentEntityForm {

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
  public function buildForm(array $form, FormStateInterface $form_state, Business $business = NULL) {
    $form = parent::buildForm($form, $form_state);

    \Drupal::service('se.form_alter')->setBusinessField($form, 'se_bu_ref', $business);

    if (!$this->entity->isNew()) {
      $form['group_co_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    return $form;
  }

}
