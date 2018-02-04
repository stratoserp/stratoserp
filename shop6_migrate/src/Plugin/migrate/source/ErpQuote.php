<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of invoice nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_quote",
 *   source_module = "erp_quote"
 * )
 */
class ErpQuote extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_quote', 'eq', 'n.nid = eq.nid');
    $query->fields('eq');

    $query->orderBy('n.nid', 'DESC');

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

    parent::setItems($row, $this->idMap, 'erp_quote_data');
    parent::setBusinessRef($row, $this->idMap);

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

    return TRUE;
  }

}
