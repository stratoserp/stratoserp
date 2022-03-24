<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\se_ticket\Entity\Ticket;
use Drupal\stratoserp\Form\StratosContentEntityForm;

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

      // Not set by parent buildForm in this case.
      $formAlter->setCustomerField($form, 'se_cu_ref', $ticket->getCustomer());

      // Add our custom redirect to stay on the ticket when submitted.
      $form['actions']['submit']['#submit'][] = '::ticketRedirect';
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
