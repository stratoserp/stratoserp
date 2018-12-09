<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\comment\Plugin\migrate\source\d6\Comment as MigrateComment;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * Migration of comments and timekeeping from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "d6_other_comment",
 *   source_module = "erp_timekeeping"
 * )
 */
class ErpOtherComment extends MigrateComment {
  use Shop6MigrateUtilities;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->condition('n.type', 'erp_job', '<>');

    $query->orderBy('cid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    $comment = $this->repairBody($row->getSourceProperty('comment'));
    $row->setSourceProperty('comment', $comment);
    $type = $row->getSourceProperty('type');

    if ($type == 'erp_job') {
      return FALSE;
    }

    if ($this->otherComment($row, $type)) {
      return TRUE;
    }

    $this->logError($row,
      t('ErpOtherComment: @nid - @cid - @type - @subject has no associated node, ignored', [
        '@nid' => $row->getSourceProperty('nid'),
        '@cid' => $row->getSourceProperty('cid'),
        '@subject' => $row->getSourceProperty('subject'),
        '@type' => $row->getSourceProperty('type')
      ]), MigrationInterface::MESSAGE_NOTICE);
    $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
    return FALSE;
  }

  /**
   * @param \Drupal\migrate\Row $row
   * @param $type
   *   Comment type.
   *
   * @return bool
   * @throws \Exception
   */
  private function otherComment(Row $row, $type) {
    if ($nid = $row->getSourceProperty('nid')) {
      $migration = NULL;
      switch ($type) {
        case 'book':
          $migration = 'upgrade_d6_node_book';
          break;
        case 'erp_customer':
          $migration = 'upgrade_d6_node_erp_customer';
          break;
        case 'erp_goods_receive':
          $migration = 'upgrade_d6_node_erp_goods_receipt';
          break;
        case 'erp_invoice':
          $migration = 'upgrade_d6_node_erp_invoice';
          break;
        case 'erp_item':
          $migration = 'upgrade_d6_node_erp_item';
          break;
        case 'erp_payment':
          $migration = 'upgrade_d6_node_erp_payment';
          break;
        case 'erp_purchase_order':
          $migration = 'upgrade_d6_node_erp_purchase_order';
          break;
        case 'erp_quote':
          $migration = 'upgrade_d6_node_erp_quote';
          break;
        default:
          return FALSE;
          break;
      }

      if ($new_id = $this->findNewId($nid, 'nid', $migration)) {
        $row->setSourceProperty('nid', $new_id);
        return TRUE;
      }
    }

    return FALSE;
  }
}
