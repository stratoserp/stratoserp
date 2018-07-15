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
    $query = parent::buildEntityQuery($match, $match_operator);

    // Extended stock query stuff here.

    return $query;
  }

}
