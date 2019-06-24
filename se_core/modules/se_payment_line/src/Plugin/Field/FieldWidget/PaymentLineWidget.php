<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldWidget;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldWidget\DynamicEntityReferenceWidget;

/**
 * Plugin implementation of the 'se_item_line_widget' widget.
 *
 * @FieldWidget(
 *   id = "se_payment_line_widget",
 *   label = @Translation("Payment line widget"),
 *   field_types = {
 *     "se_payment_line"
 *   }
 * )
 */
class PaymentLineWidget extends DynamicEntityReferenceWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $build = parent::formElement($items, $delta, $element, $form, $form_state);

//    $settings = $this->getFieldSettings();
//    $available = DynamicEntityReferenceItem::getTargetTypes($settings);
//    $target_type = $items->get($delta)->target_type ?: reset($available);
//    $entity = $items->get($delta)->entity;

    // Put a new quantity field first.
    $build['quantity'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->quantity ?: 1,
      '#size' => 8,
      '#maxlength' => 8,
      '#weight' => 4,
    ];

    // Changes to the target type field.
    $build['target_type']['#options']['comment'] = $this->t('Timekeeping');
    unset($build['target_type']['#title']);
    $build['target_type']['#weight'] = 2;

    // Adjust the weight of the target id field.
    $build['target_id']['#weight'] = 6;

    // Add a new price field.
    $build['price'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->price,
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 10,
    ];

    // Add a new price field.
    $build['serial'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->serial,
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 12,
    ];

    // Add a textarea for notes.
    $build['note'] = [
      '#type' => 'text_format',
      '#default_value' => isset($items[$delta]->note) ? $items[$delta]->note : '',
      '#rows' => 2,
      '#cols' => 30,
      '#weight' => 15
    ];

    return $build;
  }

}
