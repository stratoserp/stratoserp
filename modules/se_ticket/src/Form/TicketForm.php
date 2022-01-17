<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Ticket edit forms.
 *
 * @ingroup se_ticket
 */
class TicketForm extends StratosContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\se_ticket\Entity\Ticket $entity */
    $form = parent::buildForm($form, $form_state);

    $formAlter = \Drupal::service('se.form_alter');

    $config = \Drupal::configFactory()->get('se_ticket.settings');
    if ($ticket_status = $config->get('status_default_term')) {
      $formAlter->setTaxonomyField($form, 'se_ti_status_ref', $ticket_status);
    }

    if ($ticket_priority = $config->get('priority_default_term')) {
      $formAlter->setTaxonomyField($form, 'se_ti_priority_ref', $ticket_priority);
    }

    if ($ticket_type = $config->get('type_default_term')) {
      $formAlter->setTaxonomyField($form, 'se_ti_type_ref', $ticket_type);
    }

    return $form;
  }

}
