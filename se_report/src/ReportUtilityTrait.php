<?php

namespace Drupal\se_report;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\se_core\ErpCore;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\se_item\Entity\Item;

/**
 *
 */
trait ReportUtilityTrait {

  use RedirectDestinationTrait;

  /**
   * @var int
   */
  protected $batch_id;

  /**
   * Here just for ide checks.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $store;

  /**
   * Here just for ide checks.
   *
   * @var \Drupal\node\Entity\Node
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

    // Testing.
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
    if (!empty($this->configuration['business_ref'])
      && $business_node = Node::load($this->configuration['business_ref'])) {
      $this->report_node->field_bu_ref->target_id = $business_node->id();
    }
    $this->report_node->save();

    $this->setBatchData('report_node', $this->report_node->id());
  }

  /**
   * Take an entity and convert that to an array
   * of values for statistical reporting.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  private function convertItemLineToArray(EntityInterface $entity) {
    $data = [];

    foreach ($entity->{'field_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()] . '_lines'} as $index => $item_line) {
      if (isset($item_line->target_id)) {
        $type = $item_line->target_type;

        switch ($type) {
          // Comment type.
          case 'se_timekeeping':
            /** @var \Drupal\se_item\Entity\Item $item */
            if (!$item = Item::load($item_line->target_id)) {
              continue 2;
            }
            $item->field_it_code->value;
            $amount = $item_line->price->value * $item_line->quantity->value;
            $this->setStatisticsArray($data, $item_line, $amount);
            break;

          // The item types should be basically the same.
          case 'se_service':
          case 'se_stock':
          case 'se_recurring':
            /** @var \Drupal\se_item\Entity\Item $item */
            if (!$item = Item::load($item_line->target_id)) {
              continue 2;
            }
            $item->field_it_code->value;
            $amount = $item_line->price->value * $item_line->quantity->value;
            $this->setStatisticsArray($data, $item_line, $amount);
            break;

          case 'se_assembly':
            // TODO Recursion required? See Recursion.
            break;

          default:
            \Drupal::logger('item_breakdown_report_action')
              ->error('Unhandled item type %type.', ['%type' => $type]);
            continue 2;
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

  /**
   * @param $data
   *
   * @return bool|string
   */
  private function createCSV($data) {
    // Generate CSV data from array.
    $fh = fopen('php://temp', 'rwb');
    // Don't create a file, attempt to use memory instead.
    fputcsv($fh, ['Item', 'Value']);
    foreach ($data as $key => $value) {
      fputcsv($fh, [$key, $value]);
    }

    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);

    return $csv;
  }

  /**
   * @return string
   */
  private function createReportTitle() {
    $title_parts[] = $this->configuration['input_parameters']['action_label'];
    if (empty($this->configuration['business_ref'])) {
      if (!empty($this->configuration['input_parameters']['exposed_input']['business'])) {
        $title_parts[] = $this->configuration['input_parameters']['exposed_input']['business'];
      }
    }
    /** @var \Drupal\node\Entity\Node $business_node */
    elseif ($business_node = Node::load($this->configuration['business_ref'])) {
      $title_parts[] = $business_node->title->value;
    }

    if (!empty($this->configuration['input_parameters']['exposed_input']['created']['min'])) {
      $title_parts[] = $this->configuration['input_parameters']['exposed_input']['created']['min'];
    }
    if (!empty($this->configuration['input_parameters']['exposed_input']['created']['max'])) {
      $title_parts[] = $this->configuration['input_parameters']['exposed_input']['created']['max'];
    }
    return implode(' - ', $title_parts);
  }

  /**
   * Helper function to return the currently loaded entity from the URL (controller).
   * Returns NULL if the currently loaded page is no entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function get_current_controller_entity() {
    $currentRouteParameters = \Drupal::routeMatch()->getParameters();
    foreach ($currentRouteParameters as $param) {
      if ($param instanceof EntityInterface) {
        return $param;
      }
    }
    return NULL;
  }

  /**
   * Return a list of months for the year with
   * start and end timestamps.
   *
   * TODO - Make more flexible
   * TODO - Timezones, sigh.
   *
   * @param string $year
   *
   * @return array
   */
  public function reportingMonths($year = '') {
    $months = [];

    if (empty($year)) {
      $year = date('Y');
    }

    for ($i = 1; $i <= 12; $i++) {
      $months[date('F', mktime(0, 0, 0, $i))] = [
        'start' => mktime(0, 0, 0, $i, 1, $year),
        'end' => mktime(0, 0, 0, $i + 1, 0, $year),
      ];
    }

    return $months;
  }

  /**
   * Generate a color, darker each time the function is called.
   *
   * @param string $red
   * @param string $green
   * @param string $blue
   *
   * @return array
   *
   * @throws \Exception
   */
  public function generateColorsDarkening($red = NULL, $green = NULL, $blue = NULL) {
    static $start = 255;
    $fg = $bg = [];

    $adjustment = random_int(20, 30);
    $bg[] = empty($red) ? $start - $adjustment : $red;
    $bg[] = empty($green) ? $start - $adjustment : $green;
    $bg[] = empty($blue) ? $start - $adjustment : $blue;

    // Make fg just a derivative of the bg for now.
    foreach ($bg as $color) {
      $fg[] = max(10, $color - 20);
    }

    $fg_color = sprintf('#%02X%02X%02X', $fg[0], $fg[1], $fg[2]);
    $bg_color = sprintf('#%02X%02X%02X', $bg[0], $bg[1], $bg[2]);

    $start -= $adjustment;

    return [$fg_color, $bg_color];
  }

}
