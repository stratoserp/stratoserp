<?php

declare(strict_types=1);

namespace Drupal\se_quote\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Quote edit forms.
 *
 * @ingroup se_quote
 */
class QuoteForm extends ContentEntityForm {

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
    /** @var \Drupal\se_quote\Entity\Quote $entity */
    $form = parent::buildForm($form, $form_state);

    $service = \Drupal::service('se.form_alter');
    $service->setBusinessField($form, 'se_bu_ref');
    $service->setContactField($form, 'se_co_ref');

    if (!$this->entity->isNew()) {
      $form['group_qu_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    return $form;
  }

}
