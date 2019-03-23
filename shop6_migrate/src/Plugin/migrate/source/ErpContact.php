<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\Core\Database\Database;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * Migration of contacts from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_contact",
 *   source_module = "contact"
 * )
 */
class ErpContact extends SqlBase {
  use Shop6MigrateUtilities;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('erp_contact', 'ec');
    $query->fields('ec');

    $query->orderBy('nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'contact_id' => $this->t('Contact id (unused)'),
      'nid'        => $this->t('Associated node id'),
      'name'       => $this->t('Contact name'),
      'phone'      => $this->t('Contact phone'),
      'fax'        => $this->t('Contact fax'),
      'mobile'     => $this->t('Contact mobile'),
      'email'      => $this->t('Contact email'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'contact_id' => [
        'type'  => 'integer',
        'alias' => 'ec',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    static $taxonomy_term_map = [];

    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('contact_id'), 'contact_id')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $this->normalisePhone($row);

    $name = trim($row->getSourceProperty('name'));
    if (empty($name)) {
      if (!empty(trim($row->getSourceProperty('email')))) {
        $row->setSourceProperty('name', trim($row->getSourceproperty('email')));
      }
      else {
        $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
        return FALSE;
      }
    }

    $contact_types = [
      1 => 'General contact',
      2 => 'Accounts contact',
      3 => 'Main contact',
    ];

    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select('erp_contact_types', 'ect')
      ->fields('ect')
      ->condition('ect.contact_id', $row->getSourceProperty('contact_id'));

    $types = $query->execute()->fetchAll();

    if (count($types)) {
      $taxonomy_terms = [];
      foreach ($types as $type) {
        if (!isset($taxonomy_term_map[$contact_types[$type->type]])) {
          $term = taxonomy_term_load_multiple_by_name($contact_types[$type->type], 'se_contact_type');
          $taxonomy_term_map[$contact_types[$type->type]] = reset($term);
        }
        $taxonomy_terms[] = $taxonomy_term_map[$contact_types[$type->type]];
      }
      $row->setSourceProperty('contact_type', $taxonomy_terms);
    }

    // Business ref.
    $nid = $row->getSourceProperty('nid');
    if (!empty($nid)) {
      $new_id = $this->findNewId($nid, 'nid', 'upgrade_d6_node_erp_customer');
      $row->setSourceProperty('business_ref', $new_id);
      return TRUE;
    }
    elseif (!empty($name)) {
      $result = \Drupal::entityQuery('node')
        ->condition('type', ['se_customer', 'se_supplier'], 'IN')
        ->condition('status', 1)
        ->condition('title', $name)
        ->execute();
      $customer_nid = reset($result);

      if (!empty($customer_nid)) {
        $row->setSourceProperty('business_ref', $customer_nid);
        $this->logError($row,
          t('ErpContact: @nid - Name matched @customer as associated business for @name', [
            '@nid'      => $row->getSourceProperty('nid'),
            '@customer' => $customer_nid,
            '@name'     => $row->getSourceProperty('name'),
          ]));
        return TRUE;
      }
    }

    $this->logError($row,
      t('ErpContact: @nid - @code - @name has no associated business, ignored', [
        '@nid'  => $row->getSourceProperty('nid'),
        '@code' => $row->getSourceProperty('contact_id'),
        '@name' => $row->getSourceProperty('name'),
      ]));
    $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
    return FALSE;
  }

}
