<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Traits;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provide traits for.
 */
trait FormMultipleElementsTrait {

  /**
   * Overrides \Drupal\file\Plugin\Field\FieldWidget\WidgetBase::formMultipleElements().
   *
   * Add the recalculate button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $elements['recalculate'] = [
      '#type' => 'submit',
      '#name' => 'se_payment_lines_recalculate',
      '#value' => $this->t('Recalculate'),
      '#attributes' => ['class' => ['field-recalculate-submit']],
      '#limit_validation_errors' => $elements['add_more']['#limit_validation_errors'],
      '#submit' => [[\Drupal\se_payment_line\Controller\PaymentsController::class, 'updateFields']],
      '#ajax' => [
        'callback' => [\Drupal\se_payment_line\Controller\PaymentsController::class, 'updateFields'],
        'wrapper' => $elements['add_more']['#ajax']['wrapper'],
        'effect' => 'slide',
        'speed' => 'fast',
        'progress' => 'none',
      ],
    ];

    $elements['add_more']['#ajax']['speed'] = 'fast';
    $elements['add_more']['#ajax']['progress'] = 'none';

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    // Extract the values from $form_state->getValues().
    $path = array_merge($form['#parents'], [$field_name]);
    $key_exists = NULL;
    $values = NestedArray::getValue($form_state->getValues(), $path, $key_exists);

    if ($key_exists) {
      // Account for drag-and-drop reordering if needed.
      if (!$this->handlesMultipleValues()) {
        // Remove the 'value' of the 'add more' button.
        unset($values['add_more'], $values['recalculate']);

        // The original delta, before drag-and-drop reordering, is needed to
        // route errors to the correct form element.
        foreach ($values as $delta => &$value) {
          $value['_original_delta'] = $delta;
        }

        usort($values, function ($a, $b) {
          return SortArray::sortByKeyInt($a, $b, '_weight');
        });
      }

      // Let the widget massage the submitted values.
      $values = $this->massageFormValues($values, $form, $form_state);

      // Assign the values and remove the empty ones.
      $items->setValue($values);
      $items->filterEmptyItems();

      // Put delta mapping in $form_state, so that flagErrors() can use it.
      $field_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
      foreach ($items as $delta => $item) {
        $field_state['original_deltas'][$delta] = $item->_original_delta ?? $delta;
        unset($item->_original_delta, $item->_weight, $item->_actions);
      }
      static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
    }
  }


}
