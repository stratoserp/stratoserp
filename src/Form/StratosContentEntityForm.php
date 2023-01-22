<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Service\FormAlterInterface;
use Drupal\stratoserp\Service\SetupStatusInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implement common functionality for Stratos content forms.
 */
class StratosContentEntityForm extends ContentEntityForm {

  use RevisionableEntityTrait;

  /**
   * @var \Drupal\stratoserp\Service\FormAlterInterface
   */
  protected FormAlterInterface $formAlter;

  /**
   * @var \Drupal\stratoserp\Service\SetupStatusInterface
   */
  protected SetupStatusInterface $setupStatus;

  /**
   * Extend the parent create function to add services.
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formAlter = $container->get('se.form_alter');
    $instance->setupStatus = $container->get('se.setup_status');

    return $instance;
  }

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
    $hideFields = [];

    // Only do the setup checks if the user has it enabled (default).
    if (!$this->configFactory()->get('stratoserp.settings')->get('verify_setup_complete')) {
      // Check if there are incomplete setup steps and warn the user.
      $this->setupStatus->setupStatusError();
    }

    $form = parent::buildForm($form, $form_state);

    $this->formAlter->setCustomerField($form, 'se_cu_ref');
    $this->formAlter->setContactField($form, 'se_co_ref');

    // Create a default title for most types.
    if (!in_array($form_state->getBuildInfo()['base_form_id'], [
      'se_customer_form',
      'se_supplier_form',
      'se_contact_form',
      'se_item_form',
    ])) {
      $this->formAlter->setStandardText($form, 'name', $this->formAlter->generateTitle());
    }

    // Hackery-do to remove blank entry at bottom of item line forms.
    if (isset($form['se_item_lines']) && $max_delta = $form['se_item_lines']['widget']['#max_delta']) {
      unset($form['se_item_lines']['widget'][$max_delta]);
      $form['se_item_lines']['widget']['#max_delta'] = $max_delta - 1;
    }

    // Hackery-do to remove blank entry at bottom of payment line forms.
    if (isset($form['se_payment_lines']) && $max_delta = $form['se_payment_lines']['widget']['#max_delta']) {
      unset($form['se_payment_lines']['widget'][$max_delta]);
      $form['se_payment_lines']['widget']['#max_delta'] = $max_delta - 1;
    }

    // If the user is not an administrator, hide the revision field.
    /** @var \Drupal\stratoserp\Entity\StratosEntityBase $entity */
    $entity = $form_state->getFormObject()->getEntity();
    if ($entity->isNew() || !$this->currentUser()->hasPermission('administer content')) {
      $hideFields[] = 'revision_information';
    }
    // And even if they are, put it in the extra field group anyway.
    else {
      $form['revision_information']['#group'] = 'group_extra';
    }

    // We don't use the status field atm.
    $hideFields[] = 'status';

    $this->hideFields($form, $hideFields);

    return $form;
  }

  /**
   * Hide fields helper.
   *
   * @param array $form
   *   The form to be modified.
   * @param array $fields
   *   The fields to hide.
   */
  public function hideFields(array &$form, array $fields) {
    foreach ($fields as $field) {
      $form[$field]['#access'] = FALSE;
    }
  }

}
