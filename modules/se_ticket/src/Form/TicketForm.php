<?php

namespace Drupal\se_ticket\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Ticket edit forms.
 *
 * @ingroup se_ticket
 */
class TicketForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

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

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId($this->account->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Ticket.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Ticket.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.se_ticket.canonical', ['se_ticket' => $entity->id()]);
  }

}
