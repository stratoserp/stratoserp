<?php

namespace Drupal\se_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Monthly statistics" block.
 * @Block(
 *   id = "monthly_statistics",
 *   admin_label = @Translation("Monthly statistics"),
 * )
 */
class MonthlyStatistics extends BlockBase {

  use ReportUtilityTrait;

  public function build() {
    $content = FALSE;
    $datasets = [];
    $connection = \Drupal::database();

    for ($i = 5; $i >= 0 ; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = $connection->select('node_field_data', 'nfd');
        $query->fields('nfd', ['type']);
        $query->leftjoin('node__field_in_total', 'nft', 'nfd.nid = nft.entity_id AND nfd.vid = nft.revision_id');
        $query->addExpression('SUM(field_in_total_value)', 'total');
        $query->condition('nfd.type', 'se_invoice');
        $query->condition('nfd.created', $timestamps['start'], '>=');
        $query->condition('nfd.created', $timestamps['end'], '<');
        $query->groupBy('nfd.type');
        $result = $query->execute()->fetchAssoc();
        if ($result && count($result)) {
          $content = TRUE;
        }

        $month_data[] = \Drupal::service('se_accounting.currency_format')->formatRaw($result['total'] ?? 0);
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
          'mode' => 'dataset'
        ],
        'pointRadius' => 5,
        'pointHoverRadius' => 10,
      ];
    }

    if (!$content) {
      return [];
    }

    $build['monthly_statistics'] = [
      '#data' => [
        'labels' => array_keys($this->reportingMonths()),
        'datasets' => $datasets,
      ],
      '#graph_type' => 'line',
      '#options' => [
        'tooltips' => [
          'mode' => 'point'
        ],
        'hover' => [
          'mode' => 'dataset'
        ],
      ],
      '#id' => 'monthly_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }
}