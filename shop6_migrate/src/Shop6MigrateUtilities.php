<?php

namespace Drupal\shop6_migrate;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\shop6_migrate\Plugin\migrate\source\ErpCore;


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
   * @param string $item_identifier
   *   The identifier type, cid|nid.
   * @param string $upgrade_type
   *   Type of upgrade to lookup.
   *
   * @return bool|int
   *   Return the value, if found.
   */
  public function findNewId(
    int $old_id,
    string $item_identifier,
    string $upgrade_type = NULL) {

    static $manager = FALSE;
    static $instance = [];
    static $item_ids = [];

    if (isset($item_ids[$old_id])) {
      return $item_ids[$old_id];
    }

    // If an upgrade type is passed in, we need to lookup values from another
    // migration. This means creating a new migration manager and creating
    // an instance of the migration we want to work with.
    if (!isset($upgrade_type)) {
      /** @var \Drupal\migrate\Plugin\Migration $this->migration */
      $new_id = $this->migration->getIdMap()->lookupDestinationIds([$item_identifier => $old_id]);
      return $new_id;
    }

    // If we need to, create a manager.
    if (!$manager) {
      $manager = \Drupal::service('plugin.manager.migration');
    }

    /** @var \Drupal\migrate\Plugin\MigrateIdMapInterface $new_id_map */
    if (!isset($instance[$upgrade_type])) {
      $instance[$upgrade_type] = $manager->createInstance($upgrade_type)->getIdMap();
    }

    // Finally we can try and lookup the id.
    if ($new_id = $instance[$upgrade_type]->lookupDestinationIds([$item_identifier => $old_id])) {
      $item_ids[$old_id] = reset($new_id[0]);
      return $item_ids[$old_id];
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
    $old_db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $old_db->select('erp_invoice_data', 'eid');
    $query->fields('eid');
    $query->condition('eid.item_nid', $old_nid);
    $query->condition('eid.serial', $serial);

    $result = $query->execute();
    if ($invoice = $result->fetch()) {
      return $this->findNewId($invoice->nid, 'nid', 'upgrade_d6_node_erp_invoice');
    }
    return NULL;
  }

  /**
   * Retrieve an item by code and serial number.
   *
   * @param \Drupal\migrate\Row $row
   * @param string $code
   *   The stock code to search for.
   * @param string $serial
   *   The serial number to search for.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface
   *   Return an item if found.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function findItemBySerial(Row $row, string $code, string $serial) {
    $query = \Drupal::entityQuery('se_item')
      ->condition('type', 'se_stock')
      ->condition('field_it_code', $code)
      ->condition('field_it_serial', $serial);

    $items = $query->execute();
    if (!empty($items)) {
      $stock_item_storage = \Drupal::entityTypeManager()->getStorage('se_item');
      if ($item = $stock_item_storage->loadMultiple($items)) {
        if (count($items) > 1) {
          $this->logError($row,
            t('findItemBySerial: @serial - item not unique.', [
              '@serial' => $serial,
            ]));
        }
        return reset($item);
      }
    }

    return NULL;
  }

  /**
   * Retrieve an item by code.
   *
   * @param \Drupal\migrate\Row $row
   * @param string $code
   *   The stock code to search for.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface
   *   Return an item if found.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function findItemByCode(Row $row, string $code = NULL) {
    $query = \Drupal::entityQuery('se_item')
      ->condition('type', 'se_stock')
      ->notExists('field_it_serial');

    if (empty($code)) {
      $code = $row->getSourceProperty('code');
    }
    $query->condition('field_it_code', $code);

    $items = $query->execute();
    if (!empty($items)) {
      $stock_item_storage = \Drupal::entityTypeManager()->getStorage('se_item');
      if ($item = $stock_item_storage->loadMultiple($items)) {
        return reset($item);
      }
    }

    return NULL;
  }

  /**
   * @param $timekeeping_id
   *
   * @return bool
   */
  public function findTimekeepingById($timekeeping_id) {
    // Retrieve the associated comment for a timekeeping entry.
    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select('content_type_erp_timekeeping', 'ctet')
      ->fields('ctet')
      ->condition('ctet.nid', $timekeeping_id);
    $query->orderBy('ctet.vid', 'desc');
    $query->range(NULL, 1);

    $results = $query->execute();
    if (!$results) {
      return FALSE;
    }

    if ($timekeeping = $results->fetchAll()) {
      return reset($timekeeping);
    }
    return FALSE;
  }

  /**
   * Check/update the phone fields.
   *
   * @param \Drupal\migrate\Row $row
   *   The row to work with.
   *
   * @throws \Exception
   *   The setSourceProperty() part might throw an exception.
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
        ], TRUE)) {
          $row->setSourceProperty($field, '');
          continue;
        }

        if (strlen($new_value) === 8) {
          $new_value = $prefix . $new_value;
          $row->setSourceProperty($field, $new_value);
          continue;
        }

        if (strlen($new_value) !== 10) {
          if ($row->getSourceProperty('type')) {
            $this->logError($row,
              t('normalisePhone: @nid invalid phone @phone, blanked', [
                '@nid'        => $row->getSourceProperty('nid'),
                '@phone'      => $new_value,
              ]));
          }
          else {
            $this->logError($row,
              t('normalisePhone: @nid - @contact_id invalid phone @phone for @name, blanked', [
                '@nid'        => $row->getSourceProperty('nid'),
                '@contact_id' => $row->getSourceProperty('contact_id'),
                '@phone'      => $new_value,
                '@name'       => $row->getSourceProperty('name'),
              ]));
          }
          $row->setSourceProperty($field, '');
        }
      }
    }
  }

  /**
   * Fixup html from Drupal6.
   *
   * @param string $body
   *   The HTML body of an existing node.
   *
   * @return string
   *   Return the fixed html.
   */
  public function repairBody($body) {
    // Remove weird div's.
    $body = preg_replace('/<div>/im', '<p>', $body);
    // Remove weird div's.
    $body = preg_replace("/<\/div>\n/im", "<\/p>\n", $body);
    // Remove repeated blank lines.
    $body = preg_replace("/^(\\n|\\r|<p>&nbsp;<\/p>\\n)+/im", '', $body);
    // Remove repeated nbsp; lines.
    $body = preg_replace("/^(<p>&nbsp;<\/p>\n)+/im", "<p>&nbsp;<\/p>\n", $body);
    return $body;
  }

  /**
   * Remove junk from serial
   *
   * @param string $serial
   *   Serial number from item to cleanup.
   *
   * @return string|string[]|null
   */
  public function cleanupSerial($serial) {
    $serial = trim($serial);
    $serial = preg_replace('/^na$/i', '', $serial);
    $serial = preg_replace('/^n\/a$/i', '', $serial);
    $serial = preg_replace('/^n\/p$/i', '', $serial);
    $serial = preg_replace('/^---$/', '', $serial);
    $serial = preg_replace('/^--$/', '', $serial);
    $serial = preg_replace('/^-$/', '', $serial);
    $serial = preg_replace('/N;/', '', $serial);

    return $serial;
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
  public function logError(
    Row $row,
    string $message,
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
   * Set all the taxonomy terms for an item.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row.
   *
   * @throws \Exception
   *   setSourceProperty() might in setTaxonomyTermByName()
   */
  public function setItemTaxonomyTerms(Row $row) {
    $current_nid = $row->getSourceProperty('nid');
    $current_vid = $row->getSourceProperty('vid');

    $db = Database::getConnection('default', 'drupal_6');
    $query = $db->select('term_node', 'tn');
    $query->fields('tn');
    $query->leftJoin('term_data', 'td', 'td.tid = tn.tid');
    $query->fields('td');
    $query->condition('tn.nid', $current_nid);
    $query->condition('tn.vid', $current_vid);

    if (!$results = $query->execute()) {
      return;
    }
    $terms = $results->fetchAll();

    foreach ($terms as $term) {
      switch ($term->vid) {
        case 2:
          $destination_vocabulary = 'se_product_type';
          $destination_field = 'field_it_product_type_ref';
          ErpCore::setTaxonomyTermByName($row, $term->name, $destination_vocabulary, $destination_field);
          break;

        case 10:
          $destination_vocabulary = 'se_manufacturer';
          $destination_field = 'field_it_manufacturer_ref';
          ErpCore::setTaxonomyTermByName($row, $term->name, $destination_vocabulary, $destination_field);
          break;

        case 12:
          $destination_vocabulary = 'se_sale_category';
          $destination_field = 'field_it_sale_category_ref';
          ErpCore::setTaxonomyTermByName($row, $term->name, $destination_vocabulary, $destination_field);
          break;

      }
    }
  }

}
