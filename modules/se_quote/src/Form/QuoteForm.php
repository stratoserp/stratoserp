<?php

declare(strict_types=1);

namespace Drupal\se_quote\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Quote edit forms.
 *
 * @ingroup se_quote
 */
class QuoteForm extends ContentEntityForm {

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
    /** @var \Drupal\se_quote\Entity\Quote $entity */
    $form = parent::buildForm($form, $form_state);

    $service = \Drupal::service('stratoserp.set_field');
    $service->setBusinessField($form, 'se_bu_ref');
    $service->setContactField($form, 'se_co_ref');

    $config = \Drupal::configFactory()->get('se_quote.settings');
    if ($quote_status = $config->get('quote_status_term')) {
      $service->setTaxonomyField($form, 'se_status_ref', (int) $quote_status);
    }

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
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
        $this->messenger()->addMessage($this->t('Created the %label Quote.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Quote.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.se_quote.canonical', ['se_quote' => $entity->id()]);
  }

}
