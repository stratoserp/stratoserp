<?php

namespace Drupal\se_item_line\Plugin\Field\FieldWidget;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldWidget\DynamicEntityReferenceWidget;

/**
 * Plugin implementation of the 'se_item_line_widget' widget.
 *
 * @FieldWidget(
 *   id = "se_item_line_widget",
 *   label = @Translation("Line item widget"),
 *   field_types = {
 *     "se_item_line"
 *   }
 * )
 */
class ItemLineWidget extends DynamicEntityReferenceWidget {

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

    // Changes to the target_id field.
    $build['target_id']['#attributes']['placeholder'] = t('Item');
    $build['target_id']['#weight'] = 6;
    $build['target_id']['callback'] = 'Drupal\se_item_line\Controller\ItemsController::updatePrice';
    $build['target_id']['event'] = 'autocompleteclose change';
    $build['target_id']['progress'] = FALSE;

    // Add a new price field.
    $build['price'] = [
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay($items[$delta]->price ?: 0),
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 10,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => t('Price')
      ]
    ];

    // Add a new price field.
    $build['serial'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->serial,
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 12,
      '#attributes' => [
        'placeholder' => t('Serial')
      ]
    ];

    $build['completed_date'] = [
      '#type' => 'datetime',
      '#date_time_element' => 'none',
      '#default_value' => $items[$delta]->completed_date,
      '#weight' => 10,
      '#description' => t('Completed date')
    ];

    // Add a textarea for notes.
    $build['note'] = [
      '#type' => 'text_format',
      '#default_value' => $items[$delta]->note ?? '',
      '#rows' => 2,
      '#cols' => 30,
      '#weight' => 15
    ];

    //$build['#theme'] = 'se-item-line-widget';

    return $build;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = parent::massageFormValues($values, $form, $form_state);

    if ($form['#form_id'] !== 'node_se_invoice_edit_form') {
      return $new_values;
    }

    foreach ($new_values as $index => $line) {
      // TODO - Get this working in the ItemLineType setValue()
      // instead of here.
      $new_values[$index]['note'] = $line['note']['value'];
      $new_values[$index]['format'] = $line['note']['format'];
    }

    return $new_values;
  }

}
