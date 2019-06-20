<?php

namespace Drupal\se_line_item\Plugin\Field\FieldWidget;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldType\DynamicEntityReferenceItem;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldWidget\DynamicEntityReferenceWidget;

/**
 * Plugin implementation of the 'se_line_item_widget' widget.
 *
 * @FieldWidget(
 *   id = "se_line_item_widget",
 *   label = @Translation("Line item widget"),
 *   field_types = {
 *     "se_line_item"
 *   }
 * )
 */
class LineItemWidget extends DynamicEntityReferenceWidget {

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

    // Add a textarea for notes.
    $build['note'] = [
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->note) ? $items[$delta]->note : '',
      '#rows' => 3,
      '#cols' => 40,
      '#weight' => 15
    ];

    return $build;
  }

}
