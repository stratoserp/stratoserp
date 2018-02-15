<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of item nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_item",
 *   source_module = "erp_item"
 * )
 */
class ErpItem extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_item', 'ei', 'n.nid = ei.nid');
    $query->fields('ei', [
      'supplier_nid',
      'code',
      'barcode',
      'buy_price',
      'sell_price',
      'rrp_price',
      'active',
      'item_type',
      'full_desc',
      'item_locked',
      'in_stock',
    ]);

    $query->orderBy('nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    parent::setItemTaxonomyTerms($row);

    return TRUE;
  }

}
