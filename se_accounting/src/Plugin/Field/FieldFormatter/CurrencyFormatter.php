<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of stratoserp accounting formatter.
 *
 * @FieldFormatter(
 *   id = "se_currency_formatter",
 *   label = @Translation("Currency formatter"),
 *   field_types = {
 *     "se_currency"
 *   }
 * )
 */
class CurrencyFormatter extends FormatterBase {

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
        '#markup' => \Drupal::service('se_accounting.currency_format')->formatDisplay((int)$item->value),
      ];
    }

    return $element;
  }

}
