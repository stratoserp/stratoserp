<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
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
      'virtual'            => $this->t('Virtual'),
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
    $node = new Node();
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    $stock_nid = $row->getSourceProperty('stock_nid');

    if ($item_ref = $this->findNewId($stock_nid, 'nid', 'upgrade_d6_node_erp_item')) {
      $row->setSourceProperty('item_ref', $item_ref);
      if ($node->load($item_ref)) {
        $row->setSourceProperty('name', $node->title->value);
      }
      return TRUE;
    }

    $row->setSourceProperty('name', 'Unknown stock item');
    return TRUE;
  }

}
