<?php

namespace Drupal\se_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a "Report" block.
 * @Block(
 *   id = "report_block",
 *   admin_label = @Translation("Report block"),
 * )
 */
class ReportsBlock extends BlockBase {

  public function build() {
    $build['mychart'] = [
      '#data' => [
        'labels' => ['January', 'February', 'March'],
        'datasets' => [
          [
            'label' => 'Dataset 1',
            'data' => [180, 500, 300],
            'backgroundColor' => ['#00557f', '#00557f', '#00557f'],
            'hoverBackgroundColor' => ['#004060', '#004060', '#004060'],
          ],
          [
            'label' => 'Dataset 2',
            'data' => [200, 180, 400],
            'backgroundColor' => ['#f8413c', '#f8413c', '#f8413c'],
            'hoverBackgroundColor' => ['#9b2926', '#9b2926', '#9b2926'],
          ],
        ],
      ],
      '#graph_type' => 'bar',
      '#id' => 'mychart',
      '#type' => 'chartjs_api',
    ];

    return $build;
  }
}