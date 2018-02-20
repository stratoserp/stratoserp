<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;

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

    $query->leftJoin('content_field_serp_cu_ref', 'cfscr', 'n.nid = cfscr.nid');
    $query->addField('cfscr', 'field_serp_cu_ref_nid');

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

    if (self::findNewId($row->getSourceProperty('nid'), 'nid', $this->migration->id())) {
      return FALSE;
    }

    // Skip entries that are just the book header page.
    $body = $row->getSourceProperty('body');
    $title = $row->getSourceProperty('title');
    if ($body == $title . ' Documentation') {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    // Fix up weird newlines from older Drupal versions.
    $body = self::repairBody($body);
    $row->setSourceProperty('body', $body);

    // Find and add uploaded files.
    // @TODO check this is getting the correct vid.
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $this->select('upload', 'u')
      ->distinct()
      ->fields('u', ['fid', 'description', 'list'])
      ->condition('u.nid', $row->getSourceProperty('nid'))
      ->condition('u.vid', $row->getSourceProperty('vid'));
    $files = $query->execute()->fetchAll();

    if (count($files)) {
      $row->setSourceProperty('attachments', $files);
      parent::logError($row, $this->idMap,
        t('ErpBook: @nid - Attached files to node', [
          '@nid' => $row->getSourceProperty('nid'),
        ]), MigrationInterface::MESSAGE_NOTICE);
    }

    parent::setBusinessRef($row, $this->idMap);

    return TRUE;
  }

}
