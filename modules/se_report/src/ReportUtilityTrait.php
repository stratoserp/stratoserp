<?php

declare(strict_types=1);

namespace Drupal\se_report;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;

/**
 * Trait with common utility functions for building reports.
 *
 * @todo Lots of stuff in here no longer used.
 */
trait ReportUtilityTrait {

  use RedirectDestinationTrait;

  /**
   * Batch id is used to save the state.
   *
   * @var int
   */
  protected int $batchId;

  /**
   * Temporary storage used for generating reports.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $store;

  /**
   * Get the batch id from the url.
   *
   * Is there a better way?
   */
  private function setBatchId(): void {
    $query = \Drupal::request()->query;
    if (!$this->batchId = (int) $query->get('id')) {
      \Drupal::logger('item_breakdown_report_action')->error('Unable to get batch id to save state.');
    }
  }

  /**
   * Retrieve the name to use in the temp store.
   *
   * @return bool|string
   *   The batch name.
   */
  private function getBatchName() {
    if (!isset($this->batchId)) {
      return FALSE;
    }
    return 'batch_' . $this->batchId;
  }

  /**
   * Create/load the temp store based on the batch id.
   *
   * This is used to keep data between parts of the batch run.
   */
  private function createLoadBatchInfo(): void {
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
   * @param string $key
   *   The key of the value in the temp store.
   * @param string $value
   *   The value in the temp store.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function setBatchData(string $key, string $value): void {
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
   *   The data for the requested batch name.
   */
  private function getBatchData() {
    return $this->store->get($this->getBatchName());
  }

  /**
   * Retrieve a value for this batch.
   *
   * @param string $key
   *   The key to retrieve data for.
   *
   * @return bool
   *   The batch data.
   */
  private function getBatchDataByKey(string $key): bool {
    $batch_data = $this->getBatchData();
    if (!isset($batch_data[$key])) {
      return FALSE;
    }

    return $batch_data[$key];
  }

  /**
   * Update the data array with information about the item and amount.
   *
   * @param array $data
   *   Array of data being build for statistics.
   * @param string $item
   *   Item code string.
   * @param int $amount
   *   Item sale amount.
   */
  private function setStatisticsArray(array &$data = [], $item = NULL, $amount = 0): void {
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
   * Create a csv output file.
   *
   * @param array $data
   *   Data array.
   *
   * @return bool|string
   *   Csv string.
   */
  private function createCsv(array $data): string {
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
   * Helper function to return the currently loaded entity from the controller.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity, or null.
   */
  public function getCurrentControllerEntity(): ?EntityInterface {
    $currentRouteParameters = \Drupal::routeMatch()->getParameters();
    foreach ($currentRouteParameters as $param) {
      if ($param instanceof EntityInterface) {
        return $param;
      }
    }
    return NULL;
  }

  /**
   * Return a list of months for the year with start and end timestamps.
   *
   * @todo Make more flexible.
   * @todo Timezones?.
   *
   * @param string $year
   *   The year to report on.
   *
   * @return array
   *   Values of the months.
   */
  public function reportingPeriods($year = 0): array {

    $config = \Drupal::service('config.factory')->get('stratoserp.settings');
    $type = $config->get('statistics_style');

    if (empty($year)) {
      $year = (int) date('Y');
    }

    switch ($type) {
      case 'weekly':
        return $this->reportingWeeks($year);

      case 'monthly':
      default:
        return $this->reportingMonths($year);

    }
  }

  /**
   * Return an array for graphs.
   *
   * @param int $year
   *   The year to report on.
   *
   * @return array
   *   The array of weeks.
   */
  private function reportingWeeks($year) {
    $weeks = [];

    if ($year == date('Y')) {
      $end = min(52, date('m'));
    }
    else {
      $end = 52;
    }

    for ($i = 1; $i <= 52; $i++) {
      $week = (int) date('W');
      if ($week <= $end) {
        $weeks[date('l', mktime(0, 0, 0, $i))] = [
          'start' => strtotime(sprintf('%4dW%02d', $year, $week)),
          'end' => strtotime(sprintf('%4dW%02d', $year, $week + 1)),
        ];
      }
      else {
        $weeks[date('l', mktime(0, 0, 0, $i))] = [
          'start' => FALSE,
          'end' => FALSE,
        ];
      }
    }

    return $weeks;
  }

  /**
   * Return an array for graphs.
   *
   * @param int $year
   *   The year to report on.
   *
   * @return array
   *   The array of months.
   */
  private function reportingMonths($year) {
    $months = [];

    if ($year == date('Y')) {
      $end = min(12, date('m'));
    }
    else {
      $end = 12;
    }

    for ($i = 1; $i <= 12; $i++) {
      if (date('m', mktime(0, 0, 0, $i)) <= $end) {
        $months[date('F', mktime(0, 0, 0, $i))] = [
          'start' => mktime(0, 0, 0, $i, 1, $year),
          'end' => mktime(0, 0, 0, $i + 1, 0, $year),
        ];
      }
      else {
        $months[date('F', mktime(0, 0, 0, $i))] = [
          'start' => FALSE,
          'end' => FALSE,
        ];
      }
    }

    return $months;
  }

  /**
   * Generate a color, darker each time the function is called.
   *
   * @param string $red
   *   Holder for red value.
   * @param string $green
   *   Holder for green value.
   * @param string $blue
   *   Holder for blue value.
   *
   * @return array
   *   An array of colors.
   *
   * @throws \Exception
   */
  public function generateColorsDarkening($red = NULL, $green = NULL, $blue = NULL): array {
    static $start = 255;
    $fg = $bg = [];

    try {
      $adjustment = random_int(20, 30);
    }
    catch (\Exception $e) {
      \Drupal::logger('se_report')->warning('Insufficient entropy for random, fudging.');
      $adjustment = 20;
    }

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
