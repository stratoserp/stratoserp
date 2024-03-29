<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Plugin\Field\FieldWidget;

use Drupal\se_payment_line\Controller\PaymentLineController;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\se_accounting\Service\CurrencyFormatService;
use Drupal\se_invoice\Entity\Invoice;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'se_payment_line_widget' widget.
 *
 * @FieldWidget(
 *   id = "se_payment_line_widget",
 *   label = @Translation("Payment line widget"),
 *   field_types = {
 *     "se_payment_line"
 *   }
 * )
 */
class PaymentLineWidget extends EntityReferenceAutocompleteWidget {

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

    // Adjust the weight of the target id field.
    $build['target_id']['#title'] = t('Invoice');
    unset($build['target_id']['#title_display']);
    $build['target_id']['#weight'] = 6;
    $build['target_id']['#attributes']['placeholder'] = t('Select invoice');
    $build['target_id']['#ajax'] = [
      'callback' => 'Drupal\se_payment_line\Controller\PaymentsController::updateFields',
      'event' => 'autocompleteclose',
      'disable-refocus' => FALSE,
      'progress' => FALSE,
      'speed' => 'fast',
      'effect' => 'none',
    ];

    if ($invoice = $build['target_id']['#default_value']) {
      if ($invoice instanceof Invoice) {
        $build['invoice_amount'] = [
          '#title' => t('Invoice amount'),
          '#type' => 'textfield',
          '#disabled' => TRUE,
          '#weight' => 10,
          '#size' => 10,
          '#default_value' => $this->currencyFormat->formatDisplay((int) $invoice->getTotal()),
        ];
      }
    }

    // Add a new price field.
    $build['amount'] = [
      '#title' => t('Payment amount'),
      '#type' => 'textfield',
      '#default_value' => $this->currencyFormat->formatDisplay((int) $items[$delta]->amount),
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 60,
      '#required' => TRUE,
    ];

    // When the service/item was completed/delivered/done.
    $date = new DrupalDateTime($items[$delta]->completed_date ?? '1970-01-01', DateTimeItemInterface::STORAGE_TIMEZONE);
    $build['payment_date'] = [
      '#title' => t('Payment date'),
      '#type' => 'datetime',
      '#date_time_element' => 'none',
      '#default_value' => $date,
      '#weight' => 65,
      '#date_timezone' => date_default_timezone_get(),
    ];

    // Provide list of payment options.
    $config = \Drupal::service('config.factory')->get('se_payment.settings');
    $vocabulary = $config->get('default_payment_vocabulary');
    $term_options = [];
    if (isset($vocabulary)) {
      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $terms = $term_storage->loadByProperties(['vid' => $vocabulary]);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $tid => $term) {
        $term_options[$tid] = $term->getName();
      }
    }

    $build['payment_type'] = [
      '#title' => t('Payment type'),
      '#type' => 'select',
      '#options' => $term_options,
      '#default_value' => $items[$delta]->payment_type ?? $config->get('default_payment_term'),
      '#weight' => 70,
    ];

    // @todo This is a bit icky, how to do better.
    $build['left_prefix'] = [
      '#weight' => 1,
      '#suffix' => '<div class="se-payment-line-left">',
    ];

    $build['left_suffix'] = [
      '#weight' => 49,
      '#suffix' => '</div>',
    ];

    $build['right_prefix'] = [
      '#weight' => 50,
      '#suffix' => '<div class="se-payment-line-right">',
    ];

    $build['right_suffix'] = [
      '#weight' => 99,
      '#suffix' => '</div>',
    ];

    if (!isset($form['#attached']['library'])
      || (is_array($form['#attached']['library'])
        && !in_array('core/drupal.ajax', $form['#attached']['library'], TRUE))) {
      $form['#attached']['library'][] = 'core/drupal.ajax';
      $form['#attached']['library'][] = 'se_payment_line/se_payment_line';
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

    $elements['recalculate'] = [
      '#type' => 'submit',
      '#name' => 'se_payment_lines_recalculate',
      '#value' => $this->t('Recalculate'),
      '#attributes' => ['class' => ['field-recalculate-submit']],
      '#limit_validation_errors' => $elements['add_more']['#limit_validation_errors'],
      '#submit' => [[PaymentLineController::class, 'updateFields']],
      '#ajax' => [
        'callback' => [PaymentLineController::class, 'updateFields'],
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
   * Massage the form values on submit.
   *
   * {@inheritdoc}
   *
   * @todo There should be a way to do this in PaymentLineType setValue().
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // If not a full submit, return early.
    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#type'] !== 'submit') {
      return [];
    }

    foreach ($values as $index => $line) {
      $date = $line['payment_date'];
      $storage_date = '';
      if (!empty($date)) {
        $storage_date = $this->dateFormatter
          ->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
      }
      $values[$index]['payment_date'] = $storage_date;
      $values[$index]['amount'] = $this->currencyFormat->formatStorage($line['amount']);
    }

    return $values;
  }

}
