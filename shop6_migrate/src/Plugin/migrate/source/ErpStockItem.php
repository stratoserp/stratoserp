<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * Migration of contacts from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_stock_item",
 *   source_module = "erp_stock_item"
 * )
 */
class ErpStockItem extends SqlBase {
  use Shop6MigrateUtilities;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('erp_stock', 'es');
    $query->fields('es');

    $query->leftJoin('erp_item', 'ei', 'es.stock_nid = ei.nid');
    $query->fields('ei');
    $query->condition('ei.item_type', 'stock item');

    $query->leftJoin('node', 'n', 'n.nid = es.stock_nid');
    $query->fields('n');

    $query->orderBy('stock_nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'stock_id'           => $this->t('Stock id'),
      'stock_nid'          => $this->t('Stock nid'),
      'serial'             => $this->t('Serial'),
      'purchase_order_nid' => $this->t('Purchase order nid'),
      'receipt_nid'        => $this->t('Receipt nid'),
      'receipt_price'      => $this->t('Receipt price'),
      'sell_date'          => $this->t('Sell date'),
      'sell_price'         => $this->t('Sell price'),
      'store_nid'          => $this->t('Stock nid'),
      'lost'               => $this->t('Lost'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'stock_id' => [
        'type'  => 'integer',
        'alias' => 'es',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('stock_id'), 'stock_id', 'upgrade_d6_stock_item')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    // If there is no serial, and we already have one with no serial, its imported already.
    $serial = $this->cleanupSerial($row->getSourceProperty('serial'));
    if (empty($serial) && $this->findItemByCode($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $row->setSourceProperty('serial', $serial);
    $row->setSourceProperty('title', substr($row->getSourceProperty('title'), 0, 128));
    $this->setItemTaxonomyTerms($row);

    return TRUE;
  }

}
