<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
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

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_quote')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $this->setItems($row, 'erp_quote_data');
    if (!$this->setBusinessRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    switch ($row->getSourceProperty('quote_status')) {
      case '0':
      case 'O':
        self::setTaxonomyTermByName($row, 'Open', 'se_status', 'status_ref');
        break;

      case '1':
      case 'C':
        self::setTaxonomyTermByName($row, 'Closed', 'se_status', 'status_ref');
        break;

    }

    return TRUE;
  }

}
