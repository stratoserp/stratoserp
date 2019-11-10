<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of a widget to display cents in Human readable dollars and cents.
 *
 * @FieldWidget(
 *   id = "se_currency_widget",
 *   module = "se_accounting",
 *   label = @Translation("Currency widget"),
 *   field_types = {
 *     "se_currency"
 *   }
 * )
 */
class CurrencyWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = $items[$delta]->value ?? 0;
    $element += [
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay((int)$value),
      '#size' => 12,
      '#maxlength' => 12,
    // '#element_validate' => [
    //        [static::class, 'validate'],
    //      ],
    ];
    return ['value' => $element];
  }

  /**
   *
   */
  public static function validate($element, FormStateInterface $form_state) {
  }

  /**
   *
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    foreach ($values as &$value) {
      if (isset($value['value'])) {
        $value['value'] = \Drupal::service('se_accounting.currency_format')->formatStorage($value['value']);
      }
    }

    return $values;
  }

}
