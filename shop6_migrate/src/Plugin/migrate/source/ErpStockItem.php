<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\Core\State\StateInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\se_stock_item\Entity\StockItem;
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

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state);

    \Drupal::service('event_dispatcher')
      ->addListener(MigrateEvents::POST_ROW_SAVE,
        [$this, 'onPostRowSave']);
  }

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
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    $stock_nid = $row->getSourceProperty('stock_nid');

    if ($item_ref = self::findNewId($stock_nid, 'nid', 'upgrade_d6_node_erp_item')) {
      $row->setSourceProperty('item_ref', $item_ref);
      if ($item = Node::load($item_ref)) {
        $row->setSourceProperty('name', $item->get('title')->value);
      }
    }
    else {
      $row->setSourceProperty('name', 'Unknown stock item');
    }

    return TRUE;
  }

  public function onPostRowSave(MigratePostRowSaveEvent $event) {
    if ($event->getMigration()->getBaseId() != 'upgrade_d6_stock_item') {
      return;
    }

    $stock_item_id = $event->getDestinationIdValues();
    $row = $event->getRow();

    $stock_nid = $row->getSourceProperty('stock_nid');
    $serial = $row->getSourceProperty('serial');
    $gr_nid = $row->getSourceProperty('receipt_nid');
    $po_nid = $row->getSourceProperty('purchase_order_nid');

    $se_stock_item = StockItem::load(reset($stock_item_id));
    if ($se_stock_item) {
      $update_required = FALSE;

      if (!isset($se_stock_item->get('field_si_goods_receipt_ref')->target_id) && !empty($gr_nid)) {
        if ($gr_ref = self::findNewId($gr_nid, 'nid', 'upgrade_d6_node_erp_goods_receipt')) {
          $se_stock_item->set('field_si_goods_receipt_ref', $gr_ref);
          $update_required = TRUE;
        }
      }

      if (!isset($se_stock_item->get('field_si_purchase_order_ref')->target_id) && !empty($po_nid)) {
        if ($po_ref = self::findNewId($po_nid, 'nid', 'upgrade_d6_node_erp_purchase_order')) {
          $se_stock_item->set('field_si_purchase_order_ref', $po_ref);
          $update_required = TRUE;
        }
      }

      if (!isset($se_stock_item->get('field_si_invoice_ref')->target_id)) {
        if ($invoice_ref = self::findInvoiceBySerial($stock_nid, $serial)) {
          $se_stock_item->set('field_si_invoice_ref', $invoice_ref);
          $update_required = TRUE;
        }
      }

      if ($update_required) {
        $se_stock_item->save();
        $updated++;
      }
    }

  }

}
