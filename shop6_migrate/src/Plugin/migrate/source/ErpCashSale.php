<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of cash sale nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_cash_sale",
 *   source_module = "erp_cash_sale"
 * )
 */
class ErpCashSale extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_cash_sale', 'ec', 'n.nid = ec.nid');
    $query->fields('ec');

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

    $this->setItems($row, 'erp_cash_sale_data');
    $this->setBusinessRef($row);

    // All cash sales have to be considered closed.
    $this->setTaxonomyTermByName($row, 'Closed', 'se_status', 'status_ref');

    return TRUE;
  }

}
