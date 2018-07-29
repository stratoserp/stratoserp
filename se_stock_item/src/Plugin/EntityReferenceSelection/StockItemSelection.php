<?php

namespace Drupal\se_stock_item\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Entity reference selection.
 *
 * @EntityReferenceSelection(
 *   id = "se_stock_item",
 *   label = @Translation("Se stock item: selection"),
 *   group = "se_stock_item",
 *   weight = 0
 * )
 */
class StockItemSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $in_stock = TRUE;
    $supplier = FALSE;

    // An '@' at the front means in stock or not.
    if (strpos($match, '@') === 0) {
      $in_stock = TRUE;
      $match = ltrim($match, '@');
    }

    if (strpos($match, ':')) {
      list($supplier, $item_string) = explode(':', $match);
      // Load supplier
      $match = $item_string;
    }

    $query = parent::buildEntityQuery($match, $match_operator);

    if ($in_stock) {
      $query->condition('field_si_virtual', TRUE);
    }

    if ($supplier) {
      // Add tables and conditions to filter by supplier
    }

    return $query;
  }

}
