<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Company timekeeping statistics" block.
 *
 * @Block(
 *   id = "company_timekeeping_statistics",
 *   admin_label = @Translation("Company timekeeping statistics"),
 * )
 */
class CompanyTimekeepingStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * User timekeeping block builder.
   */
  public function build() {
    $datasets = [];

    $config = \Drupal::service('config.factory')->get('stratoserp.settings');

    if (!$config->get('statistics_display')) {
      return [];
    }

    $timeframe = $config->get('statistics_timeframe') ?: 1;
    for ($i = $timeframe; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingPeriods($year) as $timestamps) {
        if (!$timestamps['start']) {
          continue;
        }
        $query = \Drupal::entityQuery('se_timekeeping');
        $query->accessCheck(TRUE);
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $query->condition('se_billed', TRUE);
        $entity_ids = $query->execute();
        $timekeepingEntries = \Drupal::entityTypeManager()
          ->getStorage('se_timekeeping')
          ->loadMultiple($entity_ids);
        $total = 0;
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        foreach ($timekeepingEntries as $timekeeping) {
          $total += $timekeeping->se_amount->value;
        }
        if ($total > 0) {
          $month_data[] = \Drupal::service('se_timekeeping.time_format')->formatDecimal($total);
        }
        else {
          $month_data[] = '';
        }
        $fg_colors[] = $fg_color;
      }

      $datasets[] = [
        'label' => $year,
        'data' => $month_data,
        'backgroundColor' => $fg_colors,
        'borderColor' => $fg_colors,
        'hoverBackgroundColor' => $fg_colors,
        'fill' => FALSE,
        'hover' => [
          'mode' => 'dataset',
        ],
        'pointRadius' => 5,
        'pointHoverRadius' => 10,
        'tension' => '0.3',
      ];
    }

    $build['company_timekeeping_statistics'] = [
      '#data' => [
        'labels' => array_keys($this->reportingPeriods()),
        'datasets' => $datasets,
      ],
      '#graph_type' => 'line',
      '#options' => [
        'tooltips' => [
          'mode' => 'point',
        ],
        'hover' => [
          'mode' => 'dataset',
        ],
      ],
      '#id' => 'company_timekeeping_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 60,
      ],
    ];

    return $build;
  }

}
