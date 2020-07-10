<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldWidget\DynamicEntityReferenceWidget;
use Drupal\stratoserp\ErpCore;

/**
 * Plugin implementation of the 'se_item_line_widget' widget.
 *
 * @FieldWidget(
 *   id = "se_item_line_widget",
 *   label = @Translation("Item line widget"),
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

    $host_entity = $items->getEntity();
    $host_type = $host_entity->bundle();

    // Put a new quantity field first.
    $build['quantity'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->quantity ?: 1,
      '#size' => 8,
      '#maxlength' => 8,
      '#weight' => 4,
      '#ajax' => [
        'callback' => 'Drupal\se_item_line\Controller\ItemsController::updatePrice',
        'event' => 'change',
        'progress' => FALSE,
      ],
    ];

    // Changes to the target type field.
    $build['target_type']['#options']['comment'] = $this->t('Timekeeping');
    unset($build['target_type']['#title']);
    $build['target_type']['#weight'] = 2;

    // Changes to the target_id field.
    $build['target_id']['#attributes']['placeholder'] = t('Item');
    $build['target_id']['#weight'] = 6;
    $build['target_id']['#ajax'] = [
      'callback' => 'Drupal\se_item_line\Controller\ItemsController::updatePrice',
      'event' => 'autocompleteclose change',
      'progress' => FALSE,
    ];

    // Display the serial field.
    $build['serial'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->serial,
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 20,
      '#attributes' => [
        'placeholder' => t('Serial'),
      ],
    ];
    if ($host_type !== 'se_goods_receipt') {
      // Disable serial field unless its a good receipt.
      $build['serial']['#disabled'] = TRUE;
    }

    if ($host_type === 'se_invoice') {
      // When the service/item was completed/delivered/done.
      $date = new DrupalDateTime($items[$delta]->completed_date);
      $build['completed_date'] = [
        '#type' => 'datetime',
        '#date_time_element' => 'none',
        '#default_value' => $date,
        '#weight' => 30,
        '#date_timezone' => date_default_timezone_get(),
        '#description' => t('Completed date'),
      ];
    }

    // Add a new price field.
    $build['price'] = [
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay($items[$delta]->price ?: 0),
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 40,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => t('Price'),
      ],
      '#ajax' => [
        'callback' => 'Drupal\se_item_line\Controller\ItemsController::updatePrice',
        'event' => 'change',
        'progress' => FALSE,
      ],
    ];

    // Add a textarea for notes.
    $build['note'] = [
      '#type' => 'text_format',
      '#default_value' => $items[$delta]->note ?? '',
      '#rows' => 2,
      '#cols' => 30,
      '#weight' => 60,
    ];

    // TODO: This is a bit icky, how to do better.
    $build['left_prefix'] = [
      '#weight' => 1,
      '#suffix' => '<div class="se-item-line-left">',
    ];

    $build['left_suffix'] = [
      '#weight' => 49,
      '#suffix' => '</div>',
    ];

    $build['right_prefix'] = [
      '#weight' => 50,
      '#suffix' => '<div class="se-item-line-right">',
    ];

    $build['right_suffix'] = [
      '#weight' => 99,
      '#suffix' => '</div>',
    ];

    return $build;
  }

  /**
   * Massage the form values on submit.
   *
   * {@inheritdoc}
   *
   * TODO: There should be a way to do this in ItemLineType setValue().
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = parent::massageFormValues($values, $form, $form_state);

    $host_type = $form_state->getFormObject()->getEntity()->bundle();

    if (!array_key_exists($host_type, ErpCore::ITEM_LINE_NODE_BUNDLE_MAP)) {
      return $new_values;
    }

    foreach ($new_values as $index => $line) {

      // Only invoices have a completed date.
      if ($host_type === 'se_invoice') {
        $date = $line['completed_date'];
        $storage_date = '';
        if (!empty($date)) {
          $storage_date = \Drupal::service('date.formatter')
            ->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
        }
        $new_values[$index]['completed_date'] = $storage_date;
      }

      $new_values[$index]['note'] = $line['note']['value'];
      $new_values[$index]['format'] = $line['note']['format'];
      $new_values[$index]['price'] = \Drupal::service('se_accounting.currency_format')->formatStorage($line['price']);
    }

    return $new_values;
  }

}
