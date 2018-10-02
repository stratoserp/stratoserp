<?php

namespace Drupal\shop6_migrate\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * @MigrateProcessPlugin(
 *   id = "stock_item_retrieval"
 * )
 */
class ErpStockItemRetrieval extends ProcessPluginBase {
  use Shop6MigrateUtilities;

  /**
   * @param $value
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   * @param \Drupal\migrate\Row $row
   * @param $destination_property
   *
   * @return int|boolean
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $stock_id = $this->findNewId($value, 'stock_id', 'upgrade_d6_stock_item');

    if (isset($stock_id)) {
      return $stock_id;
    }
    return NULL;
  }

}
