<?php

namespace Drupal\shop6_migrate\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\se_stock_item\Entity\StockItem;
use Drupal\shop6_migrate\Shop6MigrateUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Migration of item nodes from drupal6 erp system.
 *
 * @MigrateDestination(
 *   id = "upgrade_d6_node_erp_item",
 *   source_module = "erp_item"
 * )
 */
class ErpItem extends EntityContentBase {
  use Shop6MigrateUtilities;

  /** @var string $entityType */
  public static $entityType = 'node';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return parent::create($container, $configuration, 'entity:' . static::$entityType, $plugin_definition, $migration);
  }


  public function getIds() {
    return parent::getIds();
  }

  public function fields(MigrationInterface $migration = NULL) {
    return parent::fields();
  }

  // Every item has a 'virtual item' to go with it, some
  // have stock which is done later.
  public function import(Row $row, array $old_destination_id_values = []) {
    $destination_id_values = parent::import($row, $old_destination_id_values);

    $title = $row->getSourceProperty('title');

    $stock_item = StockItem::create([
      'type' => 'se_stock_item',
      'user_id' => '1',
      'name' => $title,
      'field_si_serial' => ['value' => ''],
      'field_si_item_ref' => [['target_id' => reset($destination_id_values)]],
      'field_si_virtual' => ['value' => 1],
      'field_si_sale_date' => ['value' => 0],
    ]);
    $stock_item->save();
    $this->logError($row,
      t('stockItemCreateVirtual: @nid - added virtual - @stock_id', [
        '@nid' => $item_nid,
        '@stock_id' => $stock_item->id(),
      ]), MigrationInterface::MESSAGE_INFORMATIONAL);

    return $destination_id_values;
  }

}
