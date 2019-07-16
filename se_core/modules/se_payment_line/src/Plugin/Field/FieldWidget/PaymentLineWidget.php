<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldWidget;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\Annotation\FieldWidget;
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

//    $settings = $this->getFieldSettings();
//    $available = DynamicEntityReferenceItem::getTargetTypes($settings);
//    $target_type = $items->get($delta)->target_type ?: reset($available);
//    $entity = $items->get($delta)->entity;

    // Changes to the target type field.
//    $build['target_type']['#options']['comment'] = $this->t('Timekeeping');
//    unset($build['target_type']['#title']);
//    $build['target_type']['#weight'] = 2;

    // Adjust the weight of the target id field.
    $build['target_id']['#weight'] = 6;
    $build['target_id']['#attributes']['placeholder'] = t('Select invoice');

    // Add a new price field.
    $build['amount'] = [
      '#type' => 'textfield',
      '#default_value' => \Drupal::service('se_accounting.currency_format')->formatDisplay($items[$delta]->amount ?: 0),
      '#size' => 10,
      '#maxlength' => 20,
      '#weight' => 10,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => t('Amount')
      ]
    ];

    // When the service/item was completed/delivered/done
    $date = new DrupalDateTime($items[$delta]->completed_date);
    $build['payment_date'] = [
      '#type' => 'datetime',
      '#date_time_element' => 'none',
      '#default_value' => $date,
      '#weight' => 20,
      '#date_timezone' => drupal_get_user_timezone(),
      '#description' => t('Payment date')
    ];

    // Provide list of payment options
    $config = \Drupal::service('config.factory')->get('se_payment.settings');
    $vocabulary = $config->get('vocabulary');
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
      '#default_value' => $items[$delta]->payment_type ?? '',
      '#weight' => 30,
    ];

    return $build;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = parent::massageFormValues($values, $form, $form_state);

    if ($form['#form_id'] !== 'node_se_payment_edit_form'
      && $form['#form_id'] !== 'node_se_payment_form') {
      return $new_values;
    }

    foreach ($new_values as $index => $line) {
      $date = $line['payment_date'];
      $storage_date = '';
      if (!empty($date)) {
        $storage_date = \Drupal::service('date.formatter')->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
      }
      $new_values[$index]['payment_date'] = $storage_date;
      $new_values[$index]['amount'] = \Drupal::service('se_accounting.currency_format')->formatStorage((float)$line['amount']);
    }

    return $new_values;
  }

}
