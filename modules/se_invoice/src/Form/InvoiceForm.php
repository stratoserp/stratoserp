<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Form;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Invoice edit forms.
 *
 * @ingroup se_invoice
 */
class InvoiceForm extends StratosContentEntityForm {

  /**
   * Invoice entry specific form validations.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The submitted form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // If not a full submit, return early.
    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#type'] !== 'submit') {
      return;
    }

    $input = $form_state->getUserInput();
    foreach ($input['se_item_lines'] as $index => $line) {
      $entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($line['target_id']);

      /** @var \Drupal\se_item\Entity\Item $item */
      if (!$item = $this->entityTypeManager->getStorage('se_item')->load($entity_id)) {
        $form_state->setErrorByName('se_item_lines][' . $index . '][target_id',
          'Non existent item, unable to locate item number.');
        continue;
      }

      // Item has a parent (serialised).
      if ($item->hasParent()) {
        // Does the serial number on the item match? Error if not.
        if ($item->se_serial->value !== $line['serial']) {
          $form_state->setErrorByName('se_item_lines][' . $index . '][serial',
            'Serial/Item mismatch, use the base item when the serial number is custom.');
        }
      }
    }
  }

}
