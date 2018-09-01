<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Migration of item nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_item",
 *   source_module = "erp_item"
 * )
 */
class ErpItem extends ErpCore {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityManagerInterface $entity_manager, ModuleHandler $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_manager, $module_handler);

    \Drupal::service('event_dispatcher')
      ->addListener(MigrateEvents::POST_ROW_SAVE,
        [$this, 'onPostRowSave']);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_item', 'ei', 'n.nid = ei.nid');
    $query->fields('ei', [
      'supplier_nid',
      'code',
      'barcode',
      'buy_price',
      'sell_price',
      'rrp_price',
      'active',
      'item_type',
      'full_desc',
      'item_locked',
      'in_stock',
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

    parent::setItemTaxonomyTerms($row);

    return TRUE;
  }

  public function onPostRowSave(MigratePostRowSaveEvent $event) {
    if ($event->getMigration()->getBaseId() != 'upgrade_d6_node_erp_item') {
      return;
    }

    $row = $event->getRow();
    $type = $row->getSourceProperty('item_type');
    if ($type != 'stock item') {
      $title = $row->getSourceProperty('title');
      $new_item = $event->getDestinationIdValues();

      $this->stockItemFindCreateVirtual($row, $this->idMap, $title, reset($new_item));
    }
  }

}
