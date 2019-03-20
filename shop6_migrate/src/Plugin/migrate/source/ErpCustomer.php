<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Migration of customer nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_customer",
 *   source_module = "erp_customer"
 * )
 */
class ErpCustomer extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_customer', 'erp_customer', 'n.nid = erp_customer.nid');
    $query->fields('erp_customer', [
      'customer_id',
      'address',
      'suburb',
      'state',
      'postcode',
      'postal_address',
      'postal_suburb',
      'postal_state',
      'postal_postcode',
      'phone',
      'fax',
      'mobile',
      'email',
      'email_format',
      'homepage',
      'documentation',
    ]);

    $query->leftJoin('content_type_erp_customer', 'ctec', 'n.nid = ctec.nid');
    $query->fields('ctec', [
      'field_latitude_value',
      'field_longitude_value',
      'field_serp_cu_last_contact_value',
      'field_serp_cu_status_value',
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

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_customer')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    if ($this->findNewId($row->getSourceProperty('nid'), 'nid')) {
      return FALSE;
    }

    $this->normalisePhone($row);
    $this->setBusinessHomepage($row, 'homepage');

    return TRUE;
  }

}
