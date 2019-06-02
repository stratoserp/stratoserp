<?php

namespace Drupal\se_timekeeping\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of a time formatter.
 *
 * @FieldFormatter(
 *   id = "TimeFormater",
 *   label = @Translation("Time formatter"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class TimeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays time fields using hours and minutes.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $hours = (int)($item->value / 60);
      $minutes = $item->value % 60;
      $element[$delta] = [
        '#markup' => sprintf('%1s:%02s', $hours, $minutes),
      ];
    }

    return $element;
  }

}