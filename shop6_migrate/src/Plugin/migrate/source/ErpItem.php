<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Migration of item nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_item",
 *   source_module = "erp_item"
 * )
 */
class ErpItem extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Create our own query as we don't need some fields and
    // they cause duplicates.
    $query = $this->select('node_revisions', 'nr');
    $query->innerJoin('node', 'n', static::JOIN);
    $this->handleTranslations($query);

    $query->fields('n', [
      'nid',
      'type',
      'language',
      'status',
      'created',
      'changed',
      'comment',
      'promote',
      'moderate',
      'sticky',
      'tnid',
      'translate',
    ])
    ->fields('nr', [
      'title',
      'body',
      'teaser',
      'log',
      'format',
    ]);
    $query->addField('n', 'uid', 'node_uid');
    $query->condition('n.type', $this->configuration['node_type']);
    $query->leftJoin('erp_item', 'ei', 'n.nid = ei.nid');
    $query->fields('ei', [
      'supplier_nid',
      'code',
      'barcode',
      'buy_price',
      'sell_price',
      'rrp_price',
      'active',
      'item_type',
      'full_desc',
      'item_locked',
      'in_stock',
    ]);

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

    // Don't import duplicates.
    if ($this->findItemByCode($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $row->setSourceProperty('title', substr($row->getSourceProperty('title'), 0, 128));
    $this->setItemTaxonomyTerms($row);

    return TRUE;
  }


}
