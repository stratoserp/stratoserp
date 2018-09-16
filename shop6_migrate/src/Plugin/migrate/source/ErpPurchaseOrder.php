<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of purchase order nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_purchase_order",
 *   source_module = "erp_purchase_order"
 * )
 */
class ErpPurchaseOrder extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_purchase_order', 'epo', 'n.nid = epo.nid');
    $query->fields('epo');

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

    parent::setItems($row, $this->idMap, 'erp_purchase_order_data');
    parent::setBusinessRef($row, $this->idMap);
    parent::setSupplierRef($row, $this->idMap);

    switch ($row->getSourceProperty('invoice_status')) {
      case '0':
      case 'O':
        parent::setTaxonomyTermByName($row, 'Open', 'se_status', 'status_ref');
        break;

      case '1':
      case 'C':
        parent::setTaxonomyTermByName($row, 'Close', 'se_status', 'status_ref');
        break;

    }

    if ($ref = $row->getSourceProperty('refs')) {
      if ($new_id = parent::findNewId($ref, 'nid', 'upgrade_d6_node_erp_quote')) {
        $row->setSourceProperty('source_ref', $new_id);
      }
    }

    return TRUE;
  }

}
