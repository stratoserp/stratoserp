<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

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
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $build = parent::formElement($items, $delta, $element, $form, $form_state);

    // Adjust the weight of the target id field.
    $build['target_id']['#weight'] = 6;
    $build['target_id']['#attributes']['placeholder'] = t('Select invoice');
    $build['target_id']['#ajax'] = [
      'callback' => 'Drupal\se_payment_line\Controller\PaymentsController::updateFields',
      'event' => 'autocompleteclose change',
      'progress' => FALSE,
    ];

    // Add a new price field.
    $build['amount'] = [
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $items[$delta]->amount),
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 10,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => t('Amount'),
      ],
      '#ajax' => [
        'callback' => 'Drupal\se_payment_line\Controller\PaymentsController::updateFields',
        'event' => 'change',
        'progress' => FALSE,
      ],
    ];

    // When the service/item was completed/delivered/done.
    $date = new DrupalDateTime($items[$delta]->completed_date);
    $build['payment_date'] = [
      '#type' => 'datetime',
      '#date_time_element' => 'none',
      '#default_value' => $date,
      '#weight' => 20,
      '#date_timezone' => date_default_timezone_get(),
      '#description' => t('Payment date'),
    ];

    // Provide list of payment options.
    $config = \Drupal::service('config.factory')->get('se_payment.settings');
    $vocabulary = $config->get('default_payment_vocabulary');
    $term_options = [];
    if (isset($vocabulary)) {
      $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $terms = $term_storage->loadByProperties(['vid' => $vocabulary]);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $tid => $term) {
        $term_options[$tid] = $term->getName();
      }
    }

    $build['payment_type'] = [
      '#type' => 'select',
      '#options' => $term_options,
      '#default_value' => $items[$delta]->payment_type ?? $config->get('default_payment_term'),
      '#weight' => 30,
    ];

    return $build;
  }

  /**
   * Massage the form values on submit.
   *
   * {@inheritdoc}
   *
   * @todo There should be a way to do this in PaymentLineType setValue().
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    foreach ($values as $index => $line) {
      $date = $line['payment_date'];
      $storage_date = '';
      if (!empty($date)) {
        $storage_date = \Drupal::service('date.formatter')
          ->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
      }
      $values[$index]['payment_date'] = $storage_date;
      $values[$index]['amount'] = \Drupal::service('se_accounting.currency_format')->formatStorage($line['amount']);
    }

    return $values;
  }

}
