<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of invoice nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_goods_receipt",
 *   source_module = "erp_goods_receive"
 * )
 */
class ErpGoodsReceipt extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_goods_receive', 'egr', 'n.nid = egr.nid');
    $query->fields('egr');

    $query->orderBy('n.nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    if (self::findNewId($row->getSourceProperty('nid'), 'nid', $this->migration->id())) {
      return FALSE;
    }

    parent::setItems($row, $this->idMap, 'erp_goods_receive_data');
    parent::setSupplierRef($row, $this->idMap);

    return TRUE;
  }

}
