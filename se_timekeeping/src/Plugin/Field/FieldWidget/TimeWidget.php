<?php

namespace Drupal\se_timekeeping\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of a widget to display cents in Human readable dollars and cents.
 *
 * @FieldWidget(
 *   id = "TimeWidget",
 *   module = "se_timekeeping",
 *   label = @Translation("Time widget"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class TimeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element += [
      '#type' => 'textfield',
      '#default_value' => $value,
      '#size' => 7,
      '#maxlength' => 7,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];
    return ['value' => $element];
  }

  /**
   * Validate the color text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
  }

//  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
//    foreach ($values as &$value) {
//
//    }
//
//    return $values;
//  }

}