<?php

declare(strict_types=1);

namespace Drupal\se_item\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Item edit forms.
 *
 * @ingroup se_item
 */
class ItemForm extends StratosContentEntityForm {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if ($this->entity->isNew()) {
      $temp = 1;

      $this->hideFields($form, [
        'se_po_ref', 'se_gr_ref', 'se_in_ref',
        'se_it_ref',
        'se_lost', 'se_sold', 'se_sale_date', 'se_sale_price',
      ]);

    }

    return $form;
  }

  private function hideFields(array &$form, array $fields) {
    foreach ($fields as $field) {
      $form[$field]['#access'] = FALSE;
    }
  }

}
