<?php

namespace Drupal\se_report;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\se_core\ErpCore;
use Drupal\Core\Routing\RedirectDestinationTrait;

trait ReportUtilityTrait {

  use RedirectDestinationTrait;

  /**
   * @var int $batch_id;
   */
  protected $batch_id;

  /**
   * Here just for ide checks
   * @var PrivateTempStore $store
   */
  protected $store;

  /**
   * Here just for ide checks
   * @var Node $report_node
   */
  protected $report_node;

  /**
   * Get the batch id from the url.
   * Is there a better way?
   */
  private function setBatchId() {
    $query = \Drupal::request()->query;
    if (!$this->batch_id = $query->get('id')) {
      \Drupal::logger('item_breakdown_report_action')->error('Unable to get batch id to save state.');
    }
  }

  /**
   * Retrieve the name to use in the temp store.
   *
   * @return bool|string
   */
  private function getBatchName() {
    if (!isset($this->batch_id)) {
      return FALSE;
    }
    return 'batch_' . $this->batch_id;
  }

  /**
   * Create/load the temp store based on the batch id which is used
   * to keep data between parts of the batch run.
   *
   */
  private function createLoadBatchInfo() {
    $temp_store = \Drupal::service('tempstore.private');
    $this->store = $temp_store->get('item_breakdown_report_action');

    // Create a blank entry in the store for this batch if there is nothing
    // there already.
    if (!$this->store->get($this->getBatchName())) {
      $this->store->set($this->getBatchName(), []);
      $this->setBatchData('total', $this->context['sandbox']['total']);
      if (!empty($this->configuration['input_parameters'])) {
        $this->setBatchData('input_parameters', $this->configuration['input_parameters']);
      }
    }
  }

  /**
   * Store things for this batch.
   *
   * @param $key
   * @param $value
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function setBatchData($key, $value) {
    $batch_data = $this->getBatchData();
    $batch_data[$key] = $value;
    $this->store->set($this->getBatchName(), $batch_data);

    // Testing
    $configuration = $this->getConfiguration();
    $configuration[$this->getBatchName()] = $batch_data;
    $this->setConfiguration($configuration);
  }

  /**
   * Retrieve all data for this batch.
   *
   * @return mixed
   */
  private function getBatchData() {
    return $this->store->get($this->getBatchName());
  }

  /**
   * Retrieve a value for this batch.
   *
   * @param string $key
   *
   * @return bool
   */
  private function getBatchDataByKey(string $key) {
    $batch_data = $this->getBatchData();
    if (!isset($batch_data[$key])) {
      return FALSE;
    }

    return $batch_data[$key];
  }

  /**
   * Create/load the report node which will hold the results
   * of the batch run.
   *
   */
  private function createLoadReportNode() {
    if (($nid = $this->getBatchDataByKey('report_node')) && $node = Node::load($nid)) {
      $this->report_node = $node;
      return;
    }

    // Setup new report node to store results in.
    $this->report_node = Node::create([
      'type' => 'se_report',
      'langcode' => 'en',
      'uid' => \Drupal::currentUser()->id(),
      'status' => 1,
      'title' => $this->createReportTitle(),
    ]);
    if (!empty($this->configuration['business_ref'])) {
      if ($business_node = Node::load($this->configuration['business_ref'])) {
        $this->report_node->field_bu_ref->target_id = $business_node->id();
      }
    }
    $this->report_node->save();

    $this->setBatchData('report_node', $this->report_node->id());
  }

  /**
   * Take an entity with a set of paragraphs and convert that to an array
   * of values for statistical reporting.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  private function convertParagraphsToArray(EntityInterface $entity) {
    $data = [];

    foreach ($entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_items'} as $index => $value) {
      /** @var Paragraph $source_paragraph */
      if (!$source_paragraph = $value->entity) {
        return [];
      }

      if (isset($source_paragraph->field_it_line_item->entity)) {
        $type = $source_paragraph->field_it_line_item->entity->bundle();

        switch ($type) {
          // Comment type
          case 'se_timekeeping':
            $item = $source_paragraph->field_it_line_item->entity->field_tk_item->entity->field_it_code->value;
            $amount = $source_paragraph->field_it_price->value * $source_paragraph->field_it_quantity->value;
            $this->setStatisticsArray($data, $item, $amount);
            break;
          // The item types should be basically the same.
          case 'se_service':
          case 'se_stock':
          case 'se_recurring':
            $item = $source_paragraph->field_it_line_item->entity->field_it_code->value;
            $amount = $source_paragraph->field_it_price->value * $source_paragraph->field_it_quantity->value;
            $this->setStatisticsArray($data, $item, $amount);
            break;
          case 'se_assembly':
            break;
          default:
            \Drupal::logger('item_breakdown_report_action')
              ->error('Unhandled paragraph type %type.', ['%type' => $type]);
            continue 2;
            break;
        }
      }

    }

    return $data;
  }

  /**
   * Update the data array with information about the item and amount.
   *
   * @param $data
   *   Array of data being build for statistics
   * @param $item
   *   Item code string
   * @param $amount
   *   Item sale amount
   */
  private function setStatisticsArray(&$data = [], $item = NULL, $amount = 0) {
    if (!empty($item)) {
      if (isset($data[$item])) {
        $data[$item] += $amount;
      }
      else {
        $data[$item] = $amount;
      }
    }
  }

  private function createCSV($data) {
    // Generate CSV data from array
    $fh = fopen('php://temp', 'rw');
    // Don't create a file, attempt to use memory instead

    fputcsv($fh, ['Item', 'Value']);
    foreach ($data as $key => $value) {
      fputcsv($fh, [$key, $value]);
    }

    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);

    return $csv;
  }

  private function createReportTitle() {
    $title_parts[] = $this->configuration['input_parameters']['action_label'];
    if (empty($this->configuration['business_ref'])) {
      if (!empty($this->configuration['input_parameters']['exposed_input']['business'])) {
        $title_parts[] = $this->configuration['input_parameters']['exposed_input']['business'];
      }
    }
    else {
      /** @var Node $business_node */
      if ($business_node = Node::load($this->configuration['business_ref'])) {
        $title_parts[] = $business_node->title->value;
      }
    }
    if (!empty($this->configuration['input_parameters']['exposed_input']['created']['min'])) {
      $title_parts[] = $this->configuration['input_parameters']['exposed_input']['created']['min'];
    }
    if (!empty($this->configuration['input_parameters']['exposed_input']['created']['max'])) {
      $title_parts[] = $this->configuration['input_parameters']['exposed_input']['created']['max'];
    }
    return implode(' - ', $title_parts);
  }

}
