<?php

namespace Drupal\se_item\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\NumericFilter;
use Drupal\views\ResultRow;

/**
 * Field handler to filter on stock amounts.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("se_item_stock_count")
 */
class SeItemStockCount extends NumericFilter {

//  public function query() {
//    $this->ensureMyTable();
//    $field = $this->getField();
//
//    $info = $this->operators();
//    if (!empty($info[$this->operator]['method'])) {
//      $this->{$info[$this->operator]['method']}($field);
//    }
//  }

}
