<?php

declare(strict_types=1);

namespace Drupal\se_contact\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Contact edit forms.
 *
 * @ingroup se_contact
 */
class ContactForm extends StratosContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // @todo This should be removed and handled on the field itself now
    $config = \Drupal::configFactory()->get('se_contact.settings');
    if ($contact_type = (int) $config->get('main_contact_term')) {
      \Drupal::service('se.form_alter')->setTaxonomyField($form, 'se_type_ref', $contact_type);
    }

    return $form;
  }

}
