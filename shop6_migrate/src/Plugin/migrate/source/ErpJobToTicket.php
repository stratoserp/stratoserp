<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Migration of job nodes from drupal6 erp system to tickets.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_job",
 *   source_module = "erp_job"
 * )
 */
class ErpJobToTicket extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_job', 'erp_job', 'n.nid = erp_job.nid');
    $query->fields('erp_job', [
      'customer_nid',
      'job_id',
      'invoice_nid',
      'job_desc',
      'job_assets',
      'printed',
    ]);

    $query->leftJoin('content_type_erp_job', 'ctej', 'n.nid = ctej.nid');
    $query->fields('ctej', [
      'field_job_date_value',
      'field_job_date_value2',
      'field_job_type_value',
      'field_serp_jo_status_value',
      'field_serp_jo_priority_value',
      'field_serp_jo_submitter_value',
    ]);

    $query->leftJoin('content_field_serp_cu_ref', 'cfscr', 'n.nid = cfscr.nid');
    $query->fields('cfscr', [
      'field_serp_cu_ref_nid',
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
    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_job')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    // Find and add uploaded files.
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $this->select('upload', 'u')
      ->distinct()
      ->fields('u', ['fid', 'description', 'list'])
      ->condition('u.nid', $row->getSourceProperty('nid'))
      ->condition('u.vid', $row->getSourceProperty('vid'));
    $files = $query->execute()->fetchAll();

    if (count($files)) {
      $row->setSourceProperty('attachments', $files);
      $this->logError($row,
        t('ErpTicket: @nid - Attached files to node', [
          '@nid' => $row->getSourceProperty('nid'),
        ]), MigrationInterface::MESSAGE_NOTICE);
    }

    if (!$this->setBusinessRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    $this->setOwnerRef($row);
    $this->setStatusRef($row);
    $this->setTaxonomyTermByRef($row, 'field_job_type_value', 3, 'se_ticket_type', 'job_type_ref');
    $this->setTaxonomyTermByRef($row, 'field_jo_priority_value', 7, 'se_ticket_priority', 'job_priority_ref');

    return TRUE;
  }

  /**
   * Set the ticket status code based on the new reference field.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row.
   *
   * @throws \Exception
   */
  private function setStatusRef(Row $row) {
    // As we're changing the job status from a value to a term,
    // we need to translate it.
    switch ($row->getSourceProperty('field_serp_jo_status_value')) {
      case 0:
        $term_name = 'Open';
        break;

      case 1:
        $term_name = 'Closed';
        break;

      case 2:
        $term_name = 'Invoiced';
        break;

      case 3:
        $term_name = 'Cancelled';
        break;

      default:
        $term_name = 'Open';
        break;

    }
    $this->setTaxonomyTermByName($row, $term_name, 'se_ticket_status', 'job_status_ref');
  }

}
