<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Display cents in Human readable dollars and cents.
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
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $formState) {
    $value = $items[$delta]->value ?? 0;
    $element += [
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $value),
      '#size' => 12,
      '#maxlength' => 12,
    ];
    return ['value' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public static function validate($element, FormStateInterface $formState) {
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $formState) {
    $values = parent::massageFormValues($values, $form, $formState);

    foreach ($values as &$value) {
      if (isset($value['value'])) {
        $value['value'] = \Drupal::service('se_accounting.currency_format')->formatStorage($value['value']);
      }
    }

    return $values;
  }

}
