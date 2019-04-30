<?php

namespace Drupal\shop6_migrate\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * @MigrateProcessPlugin(
 *   id = "stock_item_invoice"
 * )
 */
class ErpStockItemInvoice extends ProcessPluginBase {
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
    $serial = $this->cleanupSerial($row->getSourceProperty('serial'));

    if (isset($value, $serial)) {
      return $this->findInvoiceBySerial($value, $serial);
    }
    return NULL;
  }

}
