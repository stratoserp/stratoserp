<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldWidget\DynamicEntityReferenceWidget;

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

    // Reverse some settings from the parent, we always want same style.
    $build['#type'] = 'container';

    $host_type = $items->getEntity()->getEntityTypeId();

    // Put quantity field first.
    // Disable qty if serial is set, there can be only one.
    // Disable qty if its timekeeping, ajax will set it.
    $build['quantity'] = [
      '#title' => t('Quantity'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->quantity ?? 1,
      '#size' => 8,
      '#maxlength' => 8,
      '#weight' => 5,
      '#states' => [
        'enabled' => [
          [
            ':input[' . $this->lineSelector($delta, 'serial') . ']' => [
              ['value' => 'Serial'],
              'or',
              ['value' => ''],
            ],
          ],
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\se_item_line\Controller\ItemLineController::updateFields',
        'event' => 'change',
        'disable-refocus' => TRUE,
        'progress' => FALSE,
      ],
    ];

    // Changes to the target type field.
    $build['target_type']['#prefix'] = '<span class="se-item">';
    $build['target_type']['#title'] = t('Item');
    $build['target_type']['#options']['se_timekeeping'] = $this->t('Timekeeping');
    $build['target_type']['#weight'] = 10;
    $build['target_type']['#ajax'] = [
      'callback' => 'Drupal\se_item_line\Controller\ItemLineController::updateFields',
      'event' => 'change',
      'disable-refocus' => TRUE,
      'progress' => FALSE,
    ];

    // Changes to the target_id field.
    $build['target_id']['#attributes']['placeholder'] = t('Item');
    $build['target_id']['#weight'] = 15;
    $build['target_id']['#ajax'] = [
      'callback' => 'Drupal\se_item_line\Controller\ItemLineController::updateFields',
      'event' => 'autocompleteclose change',
      'disable-refocus' => TRUE,
      'progress' => FALSE,
    ];

    // Display the serial field.
    // Hide the serial if it is not an item.
    $build['serial'] = [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->serial,
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 20,
      '#attributes' => [
        'placeholder' => t('Serial'),
      ],
      '#states' => [
        'visible' => [
          ':input[' . $this->lineSelector($delta, 'target_type') . ']' => [
            'value' => 'se_item',
          ],
        ],
      ],
    ];
    $build['serial']['#suffix'] = '</span>';

    if ($host_type === 'se_invoice') {
      // When the service/item was completed/delivered/done.
      $date = new DrupalDateTime($items[$delta]->completed_date);
      $build['completed_date'] = [
        '#title' => t('Completed date'),
        '#type' => 'datetime',
        '#date_time_element' => 'none',
        '#default_value' => $date,
        '#weight' => 30,
        '#date_timezone' => date_default_timezone_get(),
      ];
    }

    // Add a new price field.
    $build['price'] = [
      '#title' => t('Unit price'),
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $items[$delta]->price),
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 40,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => t('Price'),
      ],
    ];

    // Add a textarea for notes, hide text format selection in after build.
    $build['note'] = [
      '#title' => t('Notes'),
      '#type' => 'text_format',
      '#default_value' => $items[$delta]->note ?? '',
      '#weight' => 60,
      '#after_build' => ['se_item_line_hide_text_format'],
      '#resizable' => 'both',
      '#attributes' => [
        'class' => ['item-line-node'],
      ],
      '#theme_wrappers' => ['container'],
    ];

    // @todo This is a bit icky, how to do better.
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

    if (!isset($form['#attached']['library'])
    || (is_array($form['#attached']['library'])
      && !in_array('core/drupal.ajax', $form['#attached']['library'], TRUE))) {
      $form['#attached']['library'][] = 'core/drupal.ajax';
      $form['#attached']['library'][] = 'se_item_line/se_item_line';
    }

    return $build;
  }

  /**
   * Helper function to construct the state selectors.
   *
   * @param int $delta
   *   The line number.
   * @param string $field
   *   The particular field to target.
   *
   * @return string
   *   Constructed state target.
   */
  private function lineSelector(int $delta, string $field): string {
    return "name=\"se_item_lines[{$delta}][{$field}]\"";
  }

  /**
   * Massage the form values on submit.
   *
   * {@inheritdoc}
   *
   * @todo There should be a way to do this in ItemLineType setValue().
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $host_type = $form_state->getFormObject()->getEntity()->getEntityTypeId();

    foreach ($values as $index => $line) {

      // Only invoices have a completed date.
      if ($host_type === 'se_invoice') {
        $date = $line['completed_date'];
        $storage_date = '';
        if (!empty($date)) {
          $storage_date = \Drupal::service('date.formatter')
            ->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
        }
        $values[$index]['completed_date'] = $storage_date;
      }

      $values[$index]['quantity'] = $line['quantity'];
      $values[$index]['serial'] = $line['serial'];
      $values[$index]['price'] = \Drupal::service('se_accounting.currency_format')->formatStorage($line['price']);
      $values[$index]['note'] = $line['note']['value'];
      $values[$index]['format'] = $line['note']['format'];
    }

    return $values;
  }

}
