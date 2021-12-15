<?php

namespace Drupal\se_bill\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Bill edit forms.
 *
 * @ingroup se_bill
 */
class BillForm extends ContentEntityForm {

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
    /** @var \Drupal\se_bill\Entity\Bill $entity */
    $form = parent::buildForm($form, $form_state);

    \Drupal::service('se.form_alter')->setBusinessField($form, 'se_bu_ref');

    if (!$this->entity->isNew()) {
      $form['group_bi_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    return $form;
  }

}
