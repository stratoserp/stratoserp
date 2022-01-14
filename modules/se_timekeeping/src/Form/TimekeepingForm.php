<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\se_ticket\Entity\Ticket;
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
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function buildForm(array $form, FormStateInterface $form_state, Ticket $ticket = NULL) {
    $form = parent::buildForm($form, $form_state);

    if ($ticket !== NULL) {
      $formAlter = \Drupal::service('se.form_alter');
      $formAlter->setBusinessField($form, 'se_bu_ref', $ticket->getBusiness());
      $formAlter->setReferenceField($form, 'se_ti_ref', $ticket);

      $form['actions']['submit']['#submit'][] = '::ticketRedirect';
    }

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

  /**
   * Custom redirect back to the ticket.
   */
  public static function ticketRedirect(array $form, FormStateInterface $form_state) {
    $ticketId = $form_state->getValue('se_ti_ref')[0]['target_id'];
    if (isset($ticketId)) {
      $form_state->setRedirect('entity.se_ticket.canonical', ['se_ticket' => $ticketId]);
    }

  }

}
