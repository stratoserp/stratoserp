<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\comment\Plugin\migrate\source\d6\Comment as MigrateComment;
use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\shop6_migrate\Shop6MigrateUtilities;

/**
 * Migration of comments and timekeeping from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "d6_job_comment",
 *   source_module = "erp_timekeeping"
 * )
 */
class ErpJobComment extends MigrateComment {
  use Shop6MigrateUtilities;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->condition('n.type', 'erp_job');

    $order_by = &$query->getOrderBy();
    unset($order_by['c.timestamp']);
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

    if (self::findNewId($row->getSourceProperty('cid'), 'cid', $this->migration->id())) {
      return FALSE;
    }

    $comment = self::repairBody($row->getSourceProperty('comment'));
    $row->setSourceProperty('comment', $comment);
    $type = $row->getSourceProperty('type');

    if ($type != 'erp_job') {
      return FALSE;
    }

    $result = self::jobComment($row);

    if (!$result) {
      ErpCore::logError($row, $this->idMap,
        t('ErpOtherComment: @nid - @cid - @type - @subject has no associated job, ignored', [
          '@nid' => $row->getSourceProperty('nid'),
          '@cid' => $row->getSourceProperty('cid'),
          '@subject' => $row->getSourceProperty('subject'),
          '@type' => $row->getSourceProperty('type')
        ]), MigrationInterface::MESSAGE_NOTICE);
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * @param \Drupal\migrate\Row $row
   *
   * @return bool
   * @throws \Exception
   */
  private function jobComment(Row $row) {
    // Retrieve the associated timekeeping entry.
    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select('content_type_erp_timekeeping', 'ctet')
      ->fields('ctet')
      ->condition('ctet.field_serp_tk_comment_id_value', $row->getSourceProperty('cid'));
    $query->orderBy('ctet.vid', 'desc');
    $query->range(NULL, 1);
    $timekeeping_entries = $query->execute()->fetchAll();

    if (count($timekeeping_entries)) {
      foreach ($timekeeping_entries as $timekeeping) {
        $row->setSourceProperty('tk_date',
          strftime("%FT%T", (int) $timekeeping->field_serp_tk_date_value)
        );
        $row->setSourceProperty('tk_amount', $timekeeping->field_serp_tk_taken_value);
        if (!empty($timekeeping->field_serp_tk_type_nid) && $tk_id = self::findNewId($timekeeping->field_serp_tk_type_nid, 'nid', 'upgrade_d6_node_erp_item')) {
          $row->setSourceProperty('tk_item', $tk_id);
        }
        $row->setSourceProperty('tk_billable', $timekeeping->field_serp_tk_billable_value);
        $row->setSourceProperty('tk_billed', $timekeeping->field_serp_tk_billed_value);
        $row->setSourceProperty('tk_review', $timekeeping->field_serp_tk_needs_review_value);
      }
    }

    $nid = $row->getSourceProperty('nid');
    if (!empty($nid)) {
      $new_id = self::findNewId($nid, 'nid', 'upgrade_d6_node_erp_job');
      if ($new_id) {
        $row->setSourceProperty('nid', $new_id);
        return TRUE;
      }
    }
    return FALSE;
  }

}