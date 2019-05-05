<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrateIdMapInterface;

/**
 * Migration of book nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_book",
 *   source_module = "book"
 * )
 */
class ErpBookToDocument extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_customer_link', 'ecl', 'n.nid = ecl.nid');
    $query->addField('ecl', 'customer_nid');

    $query->orderBy('nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    // Skip entries that are just the book header page.
    $body = $row->getSourceProperty('body');
    $title = $row->getSourceProperty('title');
    if ($body === $title . ' Documentation') {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    // Fix up weird newlines from older Drupal versions.
    $body = $this->repairBody($body);
    $row->setSourceProperty('body', $body);

    // Find and add uploaded files.
    // @TODO check this is getting the correct vid.
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $this->select('upload', 'u')
      ->distinct()
      ->fields('u', ['fid', 'description', 'list'])
      ->condition('u.nid', $row->getSourceProperty('nid'))
      ->condition('u.vid', $row->getSourceProperty('vid'));
    $results = $query->execute();
    $files = $results->fetchAll();

    if (count($files)) {
      $row->setSourceProperty('attachments', $files);
      $this->logError($row,
        t('ErpBook: @nid - Attached files to node', [
          '@nid' => $row->getSourceProperty('nid'),
        ]));
    }

    if (!$this->setBusinessRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    return TRUE;
  }

}
