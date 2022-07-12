<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Time widget.
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
    $value = $items[$delta]->value ?? '';
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
   * Validate the  text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
  }

}
