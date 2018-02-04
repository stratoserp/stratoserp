<?php

namespace Drupal\shop_migrate\Plugin\migrate\process;

use Drupal\file\Entity\File;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Import a file as a side-effect of a migration.
 * https://evolvingweb.ca/blog/bringing-files-along-for-ride-to-d8
 *
 * @todo - remove - not used.
 *
 * @MigrateProcessPlugin(
 *   id = "file_import"
 * )
 */
class FileImport extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      // Skip this item if there's no URL.
      throw new MigrateSkipProcessException();
    }

    // Check we can load the file, return its ID.
    $file = File::load($value);

    return $file->id();
  }

}
