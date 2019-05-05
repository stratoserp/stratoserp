<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
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

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_goods_receipt')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $purchase_order_nid = $row->getSourceProperty('purchase_order_nid');
    if (!isset($purchase_order_nid)) {
      $purchase_order_nid = $row->getSourceProperty('refs');
    }

    if (isset($purchase_order_nid)) {
      if ($new_purchase_order_nid = $this->findNewId($purchase_order_nid, 'nid', 'upgrade_d6_node_erp_purchase_order')) {
        $row->setSourceProperty('purchase_order_ref', $new_purchase_order_nid);
      }
    }

    $this->setItems($row, 'erp_goods_receive_data');
    if (!$this->setSupplierRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    return TRUE;
  }

}
