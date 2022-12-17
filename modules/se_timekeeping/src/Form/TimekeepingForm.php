<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\se_ticket\Entity\Ticket;
use Drupal\stratoserp\Form\StratosContentEntityForm;
use Drupal\workflows\Entity\Workflow;

/**
 * Form controller for Timekeeping edit forms.
 *
 * @ingroup se_timekeeping
 */
class TimekeepingForm extends StratosContentEntityForm {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function buildForm(array $form, FormStateInterface $form_state, Ticket $ticket = NULL) {
    $form = parent::buildForm($form, $form_state);

    if ($ticket !== NULL) {
      $formAlter = \Drupal::service('se.form_alter');
      $formAlter->setReferenceField($form, 'se_ti_ref', $ticket);
      $formAlter->setReferenceField($form, 'se_cu_ref', $ticket->getCustomer());

      if ($this->entity->isNew()) {
        $this->hideFields($form, [
          'se_in_ref',
        ]);
      }

      // Add our custom redirect to stay on the ticket when submitted.
      $form['actions']['submit']['#submit'][] = '::ticketRedirect';

      $workflow = Workflow::load('se_ticket_workflow');
      $validTransitions = $workflow->getTypePlugin()->getTransitionsForState($ticket->se_status->value);

      $count = 0;
      /** @var \Drupal\workflows\Transition $transition */
      foreach ($validTransitions as $transition) {
        $count++;
        $form['actions'][$transition->to()->id()] = [
          '#title' => $transition->label(),
          '#value' => $transition->label(),
          '#name' => $transition->to()->id(),
          '#type' => 'submit',
          '#weight' => 40 + $count,
          '#submit' => ['::updateStatus'],
        ];
      }
    }

    return $form;
  }

  /**
   * Custom redirect back to the ticket.
   */
  public function ticketRedirect(array $form, FormStateInterface $form_state) {
    $ticketId = $form_state->getValue('se_ti_ref')[0]['target_id'];
    if (isset($ticketId)) {
      $form_state->setRedirect('entity.se_ticket.canonical', ['se_ticket' => $ticketId]);
    }

  }

  /**
   * Just updating the status of the ticket. Update the field and reload.
   */
  public function updateStatus(array &$form, FormStateInterface $form_state) {
    $clicked = $form_state->getTriggeringElement();

    $ticketId = $form_state->getValue('se_ti_ref')[0]['target_id'];
    $ticket = Ticket::load($ticketId);
    $ticket->se_status->value = $clicked['#name'];
    $ticket->save();

    \Drupal::messenger()->addStatus('Status updated');
  }

}
