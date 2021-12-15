<?php

namespace Drupal\se_ticket\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Ticket edit forms.
 *
 * @ingroup se_ticket
 */
class TicketForm extends ContentEntityForm {

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
    /** @var \Drupal\se_ticket\Entity\Ticket $entity */
    $form = parent::buildForm($form, $form_state);

    $service = \Drupal::service('se.form_alter');
    $service->setBusinessField($form, 'se_bu_ref');
    $service->setContactField($form, 'se_co_ref');

    $config = \Drupal::configFactory()->get('se_ticket.settings');
    if ($ticket_status = $config->get('status_default_term')) {
      $service->setTaxonomyField($form, 'se_ti_status_ref', $ticket_status);
    }

    if ($ticket_priority = $config->get('priority_default_term')) {
      $service->setTaxonomyField($form, 'se_ti_priority_ref', $ticket_priority);
    }

    if ($ticket_type = $config->get('type_default_term')) {
      $service->setTaxonomyField($form, 'se_ti_type_ref', $ticket_type);
    }

    if (!$this->entity->isNew()) {
      $form['group_ti_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    return $form;
  }

}
