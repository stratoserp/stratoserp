<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of invoice nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_invoice",
 *   source_module = "erp_invoice"
 * )
 */
class ErpInvoice extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_invoice', 'ei', 'n.nid = ei.nid');
    $query->fields('ei');

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

    $this->setItems($row, 'erp_invoice_data');
    $this->setBusinessRef($row);

    switch ($row->getSourceProperty('invoice_status')) {
      case '0':
      case 'O':
        $this->setTaxonomyTermByName($row, 'Open', 'se_status', 'status_ref');
        break;

      case '1':
      case 'C':
        $this->setTaxonomyTermByName($row, 'Closed', 'se_status', 'status_ref');
        break;

      case 'S':
        $this->setTaxonomyTermByName($row, 'Sales order', 'se_status', 'status_ref');
        break;

    }

    if ($ref = $row->getSourceProperty('refs')) {
      if ($new_id = $this->findNewId($ref, 'nid', 'upgrade_d6_node_erp_quote')) {
        $row->setSourceProperty('quote_ref', $new_id);
      }
    }

    return TRUE;
  }

}
