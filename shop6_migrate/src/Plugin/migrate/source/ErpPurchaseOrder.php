<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
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

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_purchase_order')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $this->setItems($row, 'erp_purchase_order_data');
    if (!$this->setBusinessRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }
    if (!$this->setSupplierRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    switch ($row->getSourceProperty('purchase_order_status')) {
      case '0':
      case 'O':
        self::setTaxonomyTermByName($row, 'Open', 'se_status', 'status_ref');
        break;

      case '1':
      case 'C':
      self::setTaxonomyTermByName($row, 'Closed', 'se_status', 'status_ref');
        break;

    }

    if ($ref = $row->getSourceProperty('refs')) {
      if ($new_id = $this->findNewId($ref, 'nid', 'upgrade_d6_node_erp_quote')) {
        $row->setSourceProperty('source_ref', $new_id);
      }
    }

    return TRUE;
  }

}
