<?php

namespace Drupal\shop6_migrate;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\se_stock_item\Entity\StockItem;

/**
 * Trait Shop6MigrateUtilities.
 *
 * Provide various helper functions for the migration.
 *
 * @package Drupal\shop6_migrate
 */
trait Shop6MigrateUtilities {

  /**
   * Retrieve the new nid given the old nid.
   *
   * @param int $old_id
   *   Node id to lookup.
   * @param string $identifier
   *   The identifier type, cid|nid.
   * @param string $upgrade_type
   *   Type of upgrade to lookup.
   *
   * @return bool|int
   *   Return the value, if found.
   */
  public function findNewId(int $old_id, string $identifier,
                            string $upgrade_type = NULL) {
    static $manager = FALSE;
    static $instance = [];
    static $ids = [];

    if (isset($ids[$old_id])) {
      return $ids[$old_id];
    }

    // If an upgrade type is passed in, we need to lookup values from another
    // migration. This means creating a new migration manager and creating
    // an instance of the migration we want to work with.
    if (!isset($upgrade_type)) {
      $new_id = $this->migration->getIdMap()->lookupDestinationIds([$identifier => $old_id]);
      return $new_id;
    }

    if (!$manager) {
      $manager = \Drupal::service('plugin.manager.migration');
    }

    /** @var \Drupal\migrate\Plugin\MigrateIdMapInterface $new_id_map */
    if (!isset($instance[$upgrade_type])) {
      $instance[$upgrade_type] = $manager->createInstance($upgrade_type)->getIdMap();
    }

    if ($new_id = $instance[$upgrade_type]->lookupDestinationIds([$identifier => $old_id])) {
      $ids[$old_id] = reset($new_id[0]);
      return $ids[$old_id];
    }

    return NULL;
  }

  /**
   * Retrieve the nid of an invoice given a serial number.
   *
   * @param int $old_nid
   *   Node id to lookup.
   * @param string $serial
   *   The serial number of the stock item.
   *
   * @return bool|int
   *   The id for the imported invoice.
   */
  public function findInvoiceBySerial(int $old_nid, string $serial) {
    // Connect to the old database to query the data table.
    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select('erp_invoice_data', 'eid');
    $query->fields('eid');
    $query->condition('eid.item_nid', $old_nid);
    $query->condition('eid.serial', $serial);

    if ($invoice = $query->execute()->fetch()) {
      $new_id = $this->findNewId($invoice->nid, 'nid', 'upgrade_d6_node_erp_invoice');

      return $new_id;
    }
    return NULL;
  }

  /**
   * Retrieve an item by nid and serial number.
   *
   * @param int $id
   * @param string $serial
   * @param bool $virtual
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|mixed
   */
  public function findItemBySerial(int $id, string $serial,
                                   bool $virtual = FALSE) {
    $query = \Drupal::entityQuery('se_stock_item')
      ->condition('field_si_item_ref', $id);
    if (!empty($serial)) {
      $query->condition('field_si_serial', $serial);
    }
    if ($virtual) {
      $query->condition('field_si_virtual', TRUE);
    }
    $items = $query->execute();

    if (!empty($items)) {
      $stock_item_storage = \Drupal::entityTypeManager()->getStorage('se_stock_item');
      if ($item = $stock_item_storage->loadMultiple($items)) {
        return reset($item);
      }
    }

    return NULL;
  }

  /**
   * Check/update the phone fields.
   *
   * @param \Drupal\migrate\Row $row
   *   The row to work with.
   *
   * @throws \Exception
   *   setSourceProperty() might.
   */
  public function normalisePhone(Row $row) {
    $fields = [
      'phone' => '08',
      'mobile' => '04',
    ];

    foreach ($fields as $field => $prefix) {
      $value = $row->getSourceProperty($field);
      $new_value = trim(preg_replace('/\D+/', '', $value));
      if (!empty($new_value)) {
        if (in_array($new_value, [
          '08', '04', '.', 'TBA', ' ', '.,', '0000', ',', 'unknown', 'N/A', 'n/a',
        ])) {
          $row->setSourceProperty($field, '');
          continue;
        }

        if (strlen($new_value) == 8) {
          $new_value = $prefix . $new_value;
          $row->setSourceProperty($field, $new_value);
          continue;
        }

        if (strlen($new_value) != 10) {
          if ($row->getSourceProperty('type')) {
            $this->logError($row,
              t('normalisePhone: @nid invalid phone @phone, blanked', [
                '@nid'        => $row->getSourceProperty('nid'),
                '@phone'      => $new_value,
              ]), MigrationInterface::MESSAGE_NOTICE);
          }
          else {
            $this->logError($row,
              t('normalisePhone: @nid - @contact_id invalid phone @phone for @name, blanked', [
                '@nid'        => $row->getSourceProperty('nid'),
                '@contact_id' => $row->getSourceProperty('contact_id'),
                '@phone'      => $new_value,
                '@name'       => $row->getSourceProperty('name'),
              ]), MigrationInterface::MESSAGE_NOTICE);
          }
          $row->setSourceProperty($field, '');
        }
      }
    }
  }

  /**
   * @param $body
   *
   * @return mixed|null|string|string[]
   */
  public function repairBody($body) {
    // Remove weird div's
    $body = preg_replace("/<div>/im", "<p>", $body);
    // Remove weird div's
    $body = preg_replace("/<\/div>\n/im", "<\/p>\n", $body);
    // Remove repeated blank lines
    $body = preg_replace("/^(\\n|\\r|<p>&nbsp;<\/p>\\n)+/im", "", $body);
    // Remove repeated nbsp; lines
    $body = preg_replace("/^(<p>&nbsp;<\/p>\n)+/im", "<p>&nbsp;<\/p>\n", $body);
    return $body;
  }


  /**
   * Log an error the occurred during a migration.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $message
   *   The message content.
   * @param int $level
   *   MigrationInterface message level.
   */
  public function logError(Row $row, string $message,
                           int $level = MigrationInterface::MESSAGE_NOTICE) {


    if ($level < MigrationInterface::MESSAGE_INFORMATIONAL) {
      $this->migration->getIdMap()->saveMessage(
        $row->getSourceIdValues(),
        $message,
        $level
      );
    }
  }

  /**
   * Create a virtual stock item for items that don't really track stock.
   *
   * @param $row
   * @param $title
   * @param $item_nid
   *
   * @return int|null|string
   */
  public function stockItemFindCreateVirtual(Row $row, string $title,
                                             int $item_nid) {

    if (!$stock_item = $this->findItemBySerial($item_nid, '', TRUE)) {
      $stock_item = StockItem::create([
        'type' => 'se_stock_item',
        'user_id' => '1',
        'name' => $title,
        'field_si_serial' => ['value' => ''],
        'field_si_item_ref' => [['target_id' => $item_nid]],
        'field_si_virtual' => ['value' => 1],
        'field_si_sale_date' => ['value' => 0],
      ]);
      $stock_item->save();
      $this->logError($row,
        t('stockItemCreateVirtual: @nid - added virtual - @stock_id', [
          '@nid' => $item_nid,
          '@stock_id' => $stock_item->id(),
        ]), MigrationInterface::MESSAGE_INFORMATIONAL);
    }
    else {
      $this->logError($row,
        t('stockItemCreateVirtual: @nid - found virtual - @stock_id', [
          '@nid' => $item_nid,
          '@stock_id' => $stock_item->id(),
        ]), MigrationInterface::MESSAGE_INFORMATIONAL);
    }

    return $stock_item->id();
  }

}
