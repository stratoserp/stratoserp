<?php

namespace Drupal\se_items\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'se_items_default_formatter'.
 *
 * @FieldFormatter(
 *   id = "se_items_default_formatter",
 *   label = @Translation("Items formatter"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions"
 *   },
 * )
 */
class ItemsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
//      $source = [
//        '#theme' => 'serial_default',
//        '#serial_id' => $item->value,
//      ];
      $elements[$delta] = [
        '#markup' => \Drupal::service('renderer')->render(''),
      ];
    }
    return $elements;
  }

}
