<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'dynamic entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "se_payment_line_formatter",
 *   label = @Translation("Payment line formatter"),
 *   description = @Translation("Payment line formatter"),
 *   field_types = {
 *     "se_payment_line"
 *   }
 * )
 */
class PaymentLineFormatter extends EntityReferenceLabelFormatter {

  public static function defaultSettings() {
    return [];
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * Re-implementation of viewElements from EntityReferenceLabelFormatter
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $host_entity = $items->getEntity();
    $host_type = $host_entity->bundle();

    $row = [];
    $rows = [];

    $headers = [
      t('Invoice'),
      t('Amount'),
      t('Payment date'),
    ];

    $cache_tags = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $uri = $entity->toUrl();

      $element = [
        '#type' => 'link',
        '#title' => $entity->title,
        '#url' => $uri,
        '#options' => $uri->getOptions(),
      ];
      if (!empty($items[$delta]->_attributes)) {
        $element['#options'] += ['attributes' => []];
        $element['#options']['attributes'] += $items[$delta]->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and shouldn't be rendered in the field template.
        unset($items[$delta]->_attributes);
      }

      $date = new DrupalDateTime($items[$delta]->completed_date, DateTimeItemInterface::STORAGE_TIMEZONE);
      $display_date = $date->getTimestamp() !== 0 ? gmdate('Y-m-d', $date->getTimestamp()) : '';

      $row = [
        render($element),
        \Drupal::service('se_accounting.currency_format')->formatDisplay($items[$delta]->amount ?? 0),
        $display_date,
      ];
    }

    // Now wrap the lines into a bundle.
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $headers,
      '#cache' => [
        '#tags' => $cache_tags,
      ]
    ];
  }

}
