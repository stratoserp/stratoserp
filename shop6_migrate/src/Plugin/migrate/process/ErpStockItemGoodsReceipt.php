<?php

namespace Drupal\shop6_migrate\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * @MigrateProcessPlugin(
 *   id = "stock_item_goods_receipt"
 * )
 */
class ErpStockItemGoodsReceipt extends ProcessPluginBase {
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
    if (isset($value)) {
      return $this->findNewId($value, 'nid', 'upgrade_d6_node_erp_goods_receipt');
    }
    return NULL;
  }

}
