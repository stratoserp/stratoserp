<?php

namespace Drupal\se_webform_integration\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Utility\WebformDateHelper;
use Drupal\webform\WebformInterface;

/**
 * Trait for webform entity reference and autocomplete widget.
 */
trait WebformIntegrationEntityReferenceWidgetTrait {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_data' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * Returns the target id element form for a single webform field widget.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   A form element array containing basic properties for the widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form elements for a single widget for this field.
   */
  abstract protected function getTargetIdElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state);

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Set item default status to open.
    if (!isset($items[$delta]->status)) {
      $items[$delta]->status = WebformInterface::STATUS_OPEN;
    }

    // Get field name.
    $field_name = $items->getName();

    // Get field input name from field parents, field name, and the delta.
    $field_parents = array_merge(
      $element['#field_parents'], [$field_name, $delta]
    );
    $field_input_name = (array_shift($field_parents)) . ('[' . implode('][', $field_parents) . ']');

    // Get target ID element.
    $target_id_element = $this->getTargetIdElement($items, $delta, $element, $form, $form_state);

    // Merge target ID and default element and set default #weight.
    // @see \Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget::formElement
    $element = [
      'target_id' => $target_id_element + $element + ['#weight' => 0],
    ];

    // Get weight.
    $weight = $element['target_id']['#weight'];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    parent::massageFormValues($values, $form, $form_state);

    // Massage open/close dates.
    // @see \Drupal\webform\WebformEntitySettingsForm::save
    // @see \Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase::massageFormValues
    foreach ($values as &$item) {
      if (isset($item['settings'])) {
        $item += $item['settings'];
        unset($item['settings']);

        if ($item['status'] === WebformInterface::STATUS_SCHEDULED) {
          $states = ['open', 'close'];
          foreach ($states as $state) {
            if (!empty($item['scheduled'][$state]) && $item['scheduled'][$state] instanceof DrupalDateTime) {
              $item[$state] = WebformDateHelper::formatStorage($item['scheduled'][$state]);
            }
            else {
              $item[$state] = '';
            }
          }
        }
        else {
          $item['open'] = '';
          $item['close'] = '';
        }
        unset($item['scheduled']);
      }
    }
    return $values;
  }

  /**
   * Validate callback to ensure that the open date <= the close date.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @see \Drupal\webform\WebformEntitySettingsForm::validateForm
   * @see \Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeWidgetBase::validateOpenClose
   */
  public function validateOpenClose(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $status = $element['status']['#value'];
    if ($status === WebformInterface::STATUS_SCHEDULED) {
      $open_date = $element['scheduled']['open']['#value']['object'];
      $close_date = $element['scheduled']['close']['#value']['object'];

      // Require open or close dates.
      if (empty($open_date) && empty($close_date)) {
        $form_state->setError($element['scheduled']['open'], $this->t('Please enter an open or close date'));
        $form_state->setError($element['scheduled']['close'], $this->t('Please enter an open or close date'));
      }

      // Make sure open date is not after close date.
      if ($open_date instanceof DrupalDateTime && $close_date instanceof DrupalDateTime) {
        if ($open_date->getTimestamp() !== $close_date->getTimestamp()) {
          $interval = $open_date->diff($close_date);
          if ($interval->invert === 1) {
            $form_state->setError($element['scheduled']['open'], $this->t('The @title close date cannot be before the open date', ['@title' => $element['#title']]));
          }
        }
      }
    }
  }

}
