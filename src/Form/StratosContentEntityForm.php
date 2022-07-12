<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;

/**
 * Implement common functionality for Stratos content forms.
 */
class StratosContentEntityForm extends ContentEntityForm {

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
    $form = parent::buildForm($form, $form_state);

    $formAlter = \Drupal::service('se.form_alter');
    $formAlter->setCustomerField($form, 'se_cu_ref');
    $formAlter->setContactField($form, 'se_co_ref');

    // Create a default title for most types.
    if (!in_array($form_state->getBuildInfo()['base_form_id'], [
      'se_customer_form',
      'se_supplier_form',
      'se_contact_form',
      'se_item_form',
    ])) {
      $formAlter->setStandardText($form, 'name', $formAlter->generateTitle());
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

    return $form;
  }

}
