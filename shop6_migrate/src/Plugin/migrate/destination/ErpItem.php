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

  public function import(Row $row, array $old_destination_id_values = []) {
    return parent::import($row, $old_destination_id_values);
  }

}
