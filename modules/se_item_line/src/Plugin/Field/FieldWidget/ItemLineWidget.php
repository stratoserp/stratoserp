<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\Field\FieldWidget;

use Drupal\se_item_line\Controller\ItemLineController;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldWidget\DynamicEntityReferenceWidget;
use Drupal\se_accounting\Service\CurrencyFormatService;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\se_accounting\Service\CurrencyFormatService
   */
  protected CurrencyFormatService $currencyFormat;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected DateFormatter $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->currencyFormat = $container->get('se_accounting.currency_format');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

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
      'speed' => 'fast',
      'effect' => 'none',
    ];

    // Changes to the target_id field.
    $build['target_id']['#attributes']['placeholder'] = t('Item');
    $build['target_id']['#weight'] = 15;
    $build['target_id']['#ajax'] = [
      'callback' => 'Drupal\se_item_line\Controller\ItemLineController::updateFields',
      'event' => 'autocompleteclose',
      'disable-refocus' => TRUE,
      'progress' => FALSE,
      'speed' => 'fast',
      'effect' => 'none',
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
      '#default_value' => $this->currencyFormat->formatDisplay((int) $items[$delta]->price),
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
   * Overrides Drupal\Core\Field\WidgetBase::formMultipleElements().
   *
   * Add the recalculate button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    // The below parts are only relevant for multiple line entries.
    if ($items->count() <= 1) {
      return $elements;
    }

    $elements['recalculate'] = [
      '#type' => 'submit',
      '#name' => 'se_item_lines_recalculate',
      '#value' => $this->t('Recalculate'),
      '#attributes' => ['class' => ['field-recalculate-submit']],
      '#limit_validation_errors' => $elements['add_more']['#limit_validation_errors'],
      '#submit' => [[ItemLineController::class, 'updateFields']],
      '#ajax' => [
        'callback' => [ItemLineController::class, 'updateFields'],
        'wrapper' => $elements['add_more']['#ajax']['wrapper'],
        'effect' => 'slide',
        'speed' => 'fast',
        'progress' => 'none',
      ],
    ];

    $elements['add_more']['#ajax']['speed'] = 'fast';
    $elements['add_more']['#ajax']['progress'] = 'none';
    $elements['add_more']['#attributes']['class'][] = 'btn btn-primary';

    return $elements;
  }

  /**
   * Overrides Drupal\Core\Field\WidgetBase::extractFormValues().
   *
   * Add support for the recalculate button.
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
    // If not a full submit, return early.
    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#type'] !== 'submit') {
      return [];
    }

    $host_type = $form_state->getFormObject()->getEntity()->getEntityTypeId();

    foreach ($values as $index => $line) {

      // Only invoices have a completed date.
      if ($host_type === 'se_invoice') {
        $date = $line['completed_date'];
        $storage_date = '';
        if (!empty($date)) {
          $storage_date = $this->dateFormatter
            ->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
        }
        $values[$index]['completed_date'] = $storage_date;
      }

      // Load it and store the cost on the invoice at the time of creation.
      switch ($values[$index]['target_type']) {
        case 'se_item':
          /** @var \Drupal\se_item\Entity\Item $item */
          $item = $this->entityTypeManager->getStorage('se_item')->load($line['target_id']);
          $values[$index]['cost'] = $item->se_cost_price->value;
          break;

        case 'se_timekeeping':
          /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timkeeping */
          $timekeeping = $this->entityTypeManager->getStorage('se_timekeeping')->load($line['target_id']);
          $item = $timekeeping->getItem();
          $values[$index]['cost'] = $item->se_cost_price->value;
          break;
      }

      $values[$index]['quantity'] = $line['quantity'];
      $values[$index]['serial'] = $line['serial'];
      $values[$index]['price'] = $this->currencyFormat->formatStorage($line['price']);
      $values[$index]['note'] = $line['note']['value'];
      $values[$index]['format'] = $line['note']['format'];
    }

    return $values;
  }

}
