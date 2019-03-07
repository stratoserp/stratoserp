<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Migration of contacts from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_service_item",
 *   source_module = "erp_item"
 * )
 */
class ErpServiceItem extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->join('erp_item', 'ei', 'ei.nid = n.nid');
    $query->fields('ei');
    $query->condition('ei.item_type', 'service');

    $query->orderBy('n.nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid'                => $this->t('Node nid'),
      'sell_price'         => $this->t('Sell price'),
      'store_nid'          => $this->t('Stock nid'),
      'lost'               => $this->t('Lost'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

//    // Connect to the old database to query the data table.
//    $db = Database::getConnection('default', 'drupal_6');
//    /** @var \Drupal\Core\Database\Query\Select $query */
//    $query = $db->select('node', 'n');
//    $query->fields('n');
//    $query->condition('n.nid', $row->getSourceProperty('stock_nid'));
//    $query->join('content_type_erp_item', 'ctei', 'ctei.nid = n.nid AND ctei.vid = n.vid');
//    $query->fields('ctei');
//    $result = $query->execute();
//    $items = $result->fetchAll();
//    $item = reset($items);

//    $result = \Drupal::entityQuery('node')
//      ->condition('type', 'erp_item', 'IN')
//      ->condition('status', 1)
//      ->condition('nid', )
//      ->execute();
//    $item = reset($result);

//    // Find and add uploaded files.
//    /** @var \Drupal\Core\Database\Query\Select $query */
//    $query = $this->select('upload', 'u')
//      ->distinct()
//      ->fields('u', ['fid', 'description', 'list'])
//      ->condition('u.nid', $row->getSourceProperty('nid'))
//      ->condition('u.vid', $row->getSourceProperty('vid'));
//    $files = $query->execute()->fetchAll();
//
//    if (count($files)) {
//      $row->setSourceProperty('attachments', $files);
//      $this->logError($row,
//        t('ErpTicket: @nid - Attached files to node', [
//          '@nid' => $row->getSourceProperty('nid'),
//        ]), MigrationInterface::MESSAGE_NOTICE);
//    }

    $row->setSourceProperty('title', substr($row->getSourceProperty('title'), 0, 128));

    return TRUE;
  }

}
