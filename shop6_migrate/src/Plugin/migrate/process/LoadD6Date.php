<?php

namespace Drupal\shop6_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin converts Drupal 6 Date fields to Drupal 8.
 *
 * @todo - remove - not used.
 *
 * @MigrateProcessPlugin(
 *   id = "load_d6_date"
 * )
 */
class LoadD6Date extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $value;
  }

}
