<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Migration of supplier nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_supplier",
 *   source_module = "erp_supplier"
 * )
 */
class ErpSupplier extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    // Join in old style content.
    $query->leftJoin('erp_supplier', 'erp_supplier', 'n.nid = erp_supplier.nid');
    $query->fields('erp_supplier', [
      'supplier_id',
      'shortname',
      'address',
      'suburb',
      'state',
      'postcode',
      'phone',
      'fax',
      'mobile',
      'email',
      'email_format',
      'homepage',
      'documentation',
      'lookup_url',
    ]);

    // Join in any other new fields.
    $query->leftJoin('content_type_erp_supplier', 'ctes', 'n.nid = ctes.nid');
    $query->fields('ctes', [
      'field_serp_su_buy_xpath_value',
      'field_serp_su_logon_multipart_value',
      'field_serp_su_logon_url_value',
      'field_serp_su_lookup_url_value',
      'field_serp_su_post_data_value',
      'field_serp_su_rrp_xpath_value',
      'field_serp_su_username_value',
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

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_supplier')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $this->normalisePhone($row);
    $this->setBusinessHomepage($row, 'homepage');

    return TRUE;
  }

}
