<?php

namespace Drupal\se_accounting\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "se_accounting_formatter",
 *   label = @Translation("Stratos ERP Formatter"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class SeAccountingFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays currency fields using format selected in settings.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = [
        '#markup' => \Drupal::service('se_accounting.currency_format')->formatDisplay($item->value),
      ];
    }

    return $element;
  }

}