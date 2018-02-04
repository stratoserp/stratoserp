<?php

namespace Drupal\shop6_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'FixFilterFormat' migrate process plugin.
 *
 * @MigrateProcess(
 *  id = "d6_filter_format_erp_filter"
 * )
 */
class FixFilterFormat extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Plugin logic goes here.
    $temp = 1;
  }

}
