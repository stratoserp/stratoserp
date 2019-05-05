<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Migration of purchase order nodes from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_node_erp_payment",
 *   source_module = "erp_payment"
 * )
 */
class ErpPayment extends ErpCore {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('erp_payment', 'epa', 'n.nid = epa.nid');
    $query->fields('epa');

    $query->orderBy('n.nid', ErpCore::IMPORT_MODE);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      return FALSE;
    }

    if (ErpCore::IMPORT_CONTINUE && $this->findNewId($row->getSourceProperty('nid'), 'nid', 'upgrade_d6_node_erp_payment')) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IMPORTED);
      return FALSE;
    }

    $this->setPayments($row, 'erp_payment_data');
    if (!$this->setBusinessRef($row)) {
      $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_IGNORED);
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Retrieve the list of items for a content type and store them as paragraphs.
   *
   * @param \Drupal\migrate\Row $row
   *   The migrate row reference to work with.
   * @param string $data_table
   *   The data table from drupal6 erp to query for items.
   *
   * @throws \Exception
   *   setSourceProperty() might
   */
  public function setPayments(Row $row, string $data_table) {

    $payment_types = [
      1 => 'Cash',
      2 => 'Eftpos',
      3 => 'Credit card',
      4 => 'Cheque',
      5 => 'Direct deposit',
      6 => 'Existing credit',
    ];

    // We need the original node id multiple times, make a variable.
    $nid = $row->getSourceProperty('nid');

    // Connect to the old database to query the data table.
    $db = Database::getConnection('default', 'drupal_6');
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = $db->select($data_table, 'ecd');
    $query->fields('ecd');
    $query->condition('ecd.nid', $nid);
    $query->orderBy('ecd.line');

    if (!$result = $query->execute()) {
      return;
    }
    $lines = $result->fetchAll();

    $payments = [];
    $total = 0;
    foreach ($lines as $line) {
      if (!$invoice = $this->findNewId($line->invoice_nid, 'nid', 'upgrade_d6_node_erp_invoice')) {
        $this->logError($row,
          t('setPayments: @nid - invoice doesn\'t exist', [
            '@nid' => $line->invoice_nid,
          ]));
        continue;
      }

      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
      $paragraph = Paragraph::create(['type' => 'se_payments']);
      $paragraph->set('field_pa_invoice', ['target_id' => $invoice]);
      $paragraph->set('field_pa_date', ['value' => $line->payment_date]);

      if (empty($line->payment_type)) {
        $line->payment_type = 1;
        $this->logError($row,
          t('setPayments: @nid - invalid payment type', [
            '@nid' => $nid,
          ]));
      }
      $term_id = self::findCreateTerm($payment_types[$line->payment_type], 'se_payment_type');

      $paragraph->set('field_pa_type_ref', ['target_id' => $term_id]);
      $paragraph->set('field_pa_amount', ['value' => $line->payment_amount]);
      $paragraph->save();

      $payments[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];

      $total += $line->payment_amount;
    }

    $row->setSourceProperty('paragraph_items', $payments);
    $row->setSourceProperty('total', $total);
  }

}
