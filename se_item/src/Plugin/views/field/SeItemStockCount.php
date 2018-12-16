<?php

namespace Drupal\se_item\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("se_item_stock_count")
 */
class SeItemStockCount extends FieldPluginBase {

  public function query() {

  }

  public function render(ResultRow $values) {
    $node = $values->_entity;
    $query = \Drupal::entityQuery('se_stock_item')
      ->condition('field_si_virtual', FALSE)
      ->condition('field_si_item_ref', $node->id());

    $items = $query->count()->execute();

    return $items;
  }

}
