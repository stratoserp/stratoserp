<?php

namespace Drupal\se_stock_item\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Stock item entities.
 */
class StockItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
