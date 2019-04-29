<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\comment\Entity\Comment;
use Drupal\node\Plugin\migrate\source\d6\Node as MigrateNode;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\se_item\Entity\Item;
use Drupal\shop6_migrate\Shop6MigrateUtilities;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;

/**
 * Core Drupal6 erp class.
 */
class ErpCore extends MigrateNode {
  use Shop6MigrateUtilities;

  // Provide a quick way to indicate resuming/full imports.
  // Beware this majorly slows things down though, debugging only.
  // Setting this to TRUE will mess with --update. Only use TRUE
  // here if you've broken an import halfway through and are
  // testing.
  public const IMPORT_CONTINUE = FALSE;

  // Provide a quick way to switch between ASC/DESC when importing.
  public const IMPORT_MODE = 'ASC';

  /**
   * Retrieve the list of items for a content type and store them as paragraphs.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $data_table
   *   The data table from drupal6 erp to query for items.
   *
   * @throws \Exception setSourceProperty() might
   */
  public function setItems(Row $row, string $data_table) {
    static $services = [];

    $nid = $row->getSourceProperty('nid');
    $node_type = $row->getSourceProperty('type');

    // Connect to the old database to query the data table.
    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select($data_table, 'ecd');
    $query->fields('ecd');
    $query->condition('ecd.nid', $nid);
    $query->join('node', 'n', 'ecd.item_nid = n.nid');
    $query->fields('n');
    $query->join('erp_item', 'ei', 'ei.nid = n.nid');
    $query->fields('ei');
    $query->orderBy('ecd.line');

    $result = $query->execute();
    if (!$result) {
      return;
    }
    $lines = $result->fetchAll();

    $items = [];
    $total = 0;
    foreach ($lines as $line) {
      unset($item, $type);
      $line->serial = $this->cleanupSerial($line->serial);

      // If no item nid, log an error and move on.
      if (empty($line->item_nid) || $line->item_nid === 0) {
        $this->logError($row,
          t('setItems: @nid - has zero item', [
            '@nid' => $nid,
          ]));
        continue;
      }

      // If no serial number, its likely to be a service.
      if (empty($line->serial)) {
        // Check static var
        if (!isset($services[$line->item_nid])) {
          if ($service_node = $this->findNewId($line->item_nid, 'nid', 'upgrade_d6_service_item')) {
            if ($service_item = Item::load($service_node)) {
              $item = $service_item->id();
              $type = 'se_item';

              // Set static var
              $services[$line->item_nid] = $service_item->id();
            }
          }
        }
        else {
          // Use existing static var.
          $item = $services[$line->item_nid];
          $type = 'se_item';
        }
      }

      // We only do these for invoices.
      if ($node_type === 'erp_invoice') {
        // Timekeeping entry
        if (empty($item) && preg_match('/TK - ([\d]+)/', $line->serial, $matches)) {
          if (($timekeeping = $this->findTimekeepingById($matches[1])) && isset($timekeeping->field_serp_tk_comment_id_value)) {
            if (($tk_entity_id = $this->findNewId($timekeeping->field_serp_tk_comment_id_value, 'cid', 'upgrade_d6_job_comment')) &&
              $comment = Comment::load($tk_entity_id)) {
              $item = $comment->id();
              $type = 'comment';
            }
          }
        }
      }

      // If no code, well, we're screwed, log and abort.
      if (empty($item) && empty($line->code)) {
        $this->logError($row,
          t('setItems: @nid - codeless item', [
            '@nid' => $nid,
          ]));
        continue;
      }

      // For invoices and goods receipts, we should be able to use serial numbers.
      if ($node_type === 'erp_invoice' || $node_type === 'erp_goods_receive') {
        if (empty($item) && !empty($line->serial) &&
          $stock_item = $this->findItemBySerial($row, $line->code, $line->serial)) {
          $item = $stock_item->id();
          $type = 'se_item';
        }
      }

      // Wow .. maybe its a non service without a serial, tsk tsk tsk.
      if (empty($item) && $non_stock_item = $this->findItemByCode($row, $line->code)) {
        $item = $non_stock_item->id();
        $type = 'se_item';
      }

      // If still no item, warn and move on with life.
      if (empty($item)) {
        $this->logError($row,
          t('setItems: @nid - can\'t identify item', [
            '@nid' => $nid,
          ]));
        continue;
      }

      // Got an item, create, populate and save a new paragraph entry.
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
      $paragraph = Paragraph::create(['type' => 'se_items']);
      $paragraph->set('field_it_line_item', [
        'target_id' => $item,
        'target_type' => $type,
      ]);
      $paragraph->set('field_it_quantity', ['value' => $line->qty]);
      $paragraph->set('field_it_price', ['value' => $line->price]);
      $paragraph->set('field_it_description', [
        'value' => $line->extra,
        'format' => 'basic_html',
      ]);
      $paragraph->set('field_it_completed_date', ['value' => $line->completed_date]);
      $paragraph->save();
      $items[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];

      $total += ($line->qty * $line->price);
    }

    // Set the paragraph items
    $row->setSourceProperty('paragraph_items', $items);
    $row->setSourceProperty('total', $total);
  }

  /**
   * Set the supplier id for the passed row.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $field
   *   The field to set.
   *
   * @return bool
   *   Whether a business was found or not.
   * @throws \Exception
   *   setSourceProperty() might
   */
  public function setSupplierRef(Row $row,
                                 string $field = 'supplier_ref') {

    if ($supplier_nid = $row->getSourceProperty('supplier_nid')) {
      if ($new_id = $this->findNewId($supplier_nid, 'nid', 'upgrade_d6_node_erp_supplier')) {
        $row->setSourceProperty($field, $new_id);
        return TRUE;
      }
    }

    $this->logError($row,
      t('setBusinessRef: @nid - @title could not match supplier', [
        '@nid'   => $row->getSourceProperty('nid'),
        '@title' => $row->getSourceProperty('title'),
      ]));

    return FALSE;
  }

  /**
   * Set the customer id for the passed row.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $field
   *   The field to set.
   *
   * @return bool
   *   Whether a business was found or not.
   * @throws \Exception
   *   setSourceProperty() might
   */
  public function setBusinessRef(Row $row,
                                 string $field = 'business_ref') {

    if ($customer_nid = $row->getSourceProperty('customer_nid')) {
      $new_id = $this->findNewId($customer_nid, 'nid', 'upgrade_d6_node_erp_customer');
      if ($new_id) {
        $row->setSourceProperty($field, $new_id);
        return TRUE;
      }
    }

    if ($customer_nid = $row->getSourceProperty('field_serp_cu_ref_nid')) {
      $new_id = $this->findNewId($customer_nid, 'nid', 'upgrade_d6_node_erp_customer');
      if ($new_id) {
        $row->setSourceProperty($field, $new_id);
        return TRUE;
      }
    }

    // Have to try harder.
    $nid = $row->getSourceProperty('nid');
    if ($row->getSourceProperty('type') === 'book') {
      $db = Database::getConnection('default', 'drupal_6');
      /** @var \Drupal\Core\Database\Query\Select $query */
      $query = $db->select('erp_customer_link', 'ecl')
        ->fields('ecl', ['customer_nid'])
        ->condition('ecl.nid', $nid);
      $result = $query->execute();
      $customer_nid = $result->fetchField();

      // If there was one, cool.
      if ($customer_nid) {
        $new_id = $this->findNewId($customer_nid, 'nid', 'upgrade_d6_node_erp_customer');
        if ($new_id) {
          $row->setSourceProperty($field, $new_id);
          $this->logError($row,
            t('setBusinessRef: @nid - Customer link matched with @customer', [
              '@nid'      => $row->getSourceProperty('nid'),
              '@customer' => $new_id,
            ]));
          return TRUE;
        }
      }

      // Try loading the customer using the book title.
      $name = trim($row->getSourceProperty('title'));
      $result = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'se_customer')
        ->condition('title', $name)
        ->execute();
      $customer_nid = reset($result);
      if ($customer_nid) {
        $this->logError($row,
          t('setBusinessRef: @nid - Name matched @customer for @name', [
            '@nid'      => $row->getSourceProperty('nid'),
            '@customer' => $customer_nid,
            '@name'     => $name,
          ]));
        return TRUE;
      }

      // Ok, give up on it being a customer, try loading supplier by title.
      $result = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'se_supplier')
        ->condition('title', $name)
        ->execute();
      $supplier_nid = reset($result);

      if ($supplier_nid) {
        $new_id = $this->findNewId($supplier_nid, 'nid', 'upgrade_d6_node_erp_supplier');
        if ($new_id) {
          $row->setSourceProperty($field, $new_id);
          $this->logError($row,
            t('setBusinessRef: @nid - Name matched with @supplier', [
              '@nid'      => $row->getSourceProperty('nid'),
              '@supplier' => $new_id,
            ]));
          return TRUE;
        }
      }
    }

    $this->logError($row,
      t('setBusinessRef: @nid - @title could not match with a business', [
        '@nid'   => $row->getSourceProperty('nid'),
        '@title' => $row->getSourceProperty('title'),
      ]));

    return FALSE;
  }

  /**
   * Get the list of users that are assigned to a job and set them.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row.
   *
   * @throws \Exception
   *   setSourceProperty() might
   */
  public function setOwnerRef(Row $row) {
    static $user_map = [];

    $nid = $row->getSourceProperty('nid');

    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select('content_field_serp_jo_owner', 'cfsjo')
      ->fields('cfsjo')
      ->condition('cfsjo.nid', $nid);
    $result = $query->execute();
    $owners = $result->fetchAll();

    $users = [];
    foreach ($owners as $user_object) {
      if (!isset($user_map[$user_object->field_serp_jo_owner_uid])) {
        $user_map[$user_object->field_serp_jo_owner_uid] = User::load($user_object->field_serp_jo_owner_uid);
      }
      $users[] = $user_map[$user_object->field_serp_jo_owner_uid];
    }
    $row->setSourceProperty('owner_ref', $users);
  }

  /**
   * Process the website field to ensure its valid and can be stored.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $field
   *   The field name from the row that contains the homepage.
   *
   * @throws \Exception
   *   setSourceProperty() might
   */
  public function setBusinessHomepage(Row $row,
                                      string $field) {
    $message = NULL;
    $status = NULL;

    $homepage = trim($row->getSourceProperty($field));
    if (!empty($homepage)) {
      // Try and fix up common issues.
      if (strpos($homepage, 'http') !== 0 && strpos($homepage, 'www') === 0) {
        $homepage = 'http://' . $homepage;
      }
      try {
        $row->setSourceProperty($field . '_uri', Url::fromUri($homepage)
          ->toString());
      }
      catch (\InvalidArgumentException $e) {
        $this->logError($row,
          t('setBusinessHomepage: @nid - @code invalid url, ignored', [
            '@nid'  => $row->getSourceProperty('nid'),
            '@code' => $homepage,
          ]));
      }
    }
  }

  /**
   * Set taxonomy term.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row.
   * @param string $source_field
   *   The field in the source record to retrieve.
   * @param int $source_vocabulary
   *   The source vocabulary id.
   * @param string $destination_vocabulary
   *   The destination vocabulary.
   * @param string $destination_field
   *   The Destination field.
   *
   * @throws \Exception
   *   setSourceProperty() might in setTaxonomyTermByName()
   */
  public function setTaxonomyTermByRef(Row $row,
                                       string $source_field,
                                       int $source_vocabulary,
                                       string $destination_vocabulary,
                                       string $destination_field) {
    $term_id = $row->getSourceProperty($source_field);

    if (isset($term_id)) {
      $term_name = $this->getTermNameById($term_id, $source_vocabulary);

      if (!empty($term_name)) {
        self::setTaxonomyTermByName($row, $term_name, $destination_vocabulary, $destination_field);
      }
    }
  }

  /**
   * Lookup a taxonomy term and set the corresponding field.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $name
   *   The field name from the row.
   * @param string $vocabulary
   *   The taxonomy vocabulary to load the term from.
   * @param string $destination
   *   The field name to be set.
   *
   * @throws \Exception
   *   setSourceProperty() might.
   */
  public static function setTaxonomyTermByName(Row $row,
                                        string $name,
                                        string $vocabulary,
                                        string $destination) {

    $id = self::findCreateTerm($name, $vocabulary);
    $static_term_id_cache[$vocabulary][$name] = $id;
    $row->setSourceProperty($destination, $id);
  }

  /**
   * @param $name
   * @param $vocabulary
   *
   * @return mixed
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function findCreateTerm(string $name, string $vocabulary) {
    static $static_term_name_cache = [];

    if ($id = $static_term_name_cache[$vocabulary][$name]) {
      return $id;
    }

    $terms = taxonomy_term_load_multiple_by_name($name, $vocabulary);

    if (!$terms) {
      Term::create([
        'parent' => [],
        'name'   => $name,
        'vid'    => $vocabulary,
      ])->save();

      // Now retrieve the term again.
      $terms = taxonomy_term_load_multiple_by_name($name, $vocabulary);
    }

    $term = reset($terms);
    $static_term_name_cache[$vocabulary][$name] = $term->id();

    return $term->id();
  }

  /**
   * Lookup a taxonomy term from the d6 database by term id and vocab id.
   *
   * @param int $term_id
   *   The d6 taxonomy term id.
   * @param int $old_vid
   *   The d6 taxonomy term vid.
   *
   * @return null|string
   *   Return the taxonomy term.
   */
  public function getTermNameById(int $term_id,
                                  int $old_vid) {
    static $static_term_id_name_cache = [];

    if (!isset($term_id)) {
      return NULL;
    }

    if (!isset($static_term_id_name_cache[$old_vid][$term_id])) {
      $db = Database::getConnection('default', 'drupal_6');
      /** @var \Drupal\Core\Database\Query\Select $query */
      $query = $db->select('term_data', 'td');
      $query->fields('td', [
        'name',
      ]);
      $query->condition('td.tid', $term_id);
      $query->condition('td.vid', $old_vid);
      $result = $query->execute();
      $d6_term = $result->fetch();
      if ($d6_term) {
        $static_term_id_name_cache[$old_vid][$term_id] = $d6_term->name;
      }
      else {
        return NULL;
      }
    }

    return $static_term_id_name_cache[$old_vid][$term_id];
  }

}
