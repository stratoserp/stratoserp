<?php

namespace Drupal\se_timekeeping\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Timekeeping statistics user" block.
 * @Block(
 *   id = "user_timekeeping_statistics",
 *   admin_label = @Translation("Timekeeping statistics per user"),
 * )
 */
class TimekeepingStatisticsUser extends BlockBase {

  use ReportUtilityTrait;

  public function build() {
    $datasets = [];

    /** @var EntityInterface $node */
    if (!$entity = $this->get_current_controller_entity()) {
      return [];
    }

    if ($entity->getEntityTypeId() !== 'user') {
      return [];
    }

    for ($i = 5; $i >= 0 ; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('comment');
        $query->condition('comment_type', 'se_timekeeping');
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $query->condition('field_tk_billed', TRUE);
        $entity_ids = $query->execute();
        $comments = \Drupal::entityTypeManager()
          ->getStorage('comment')
          ->loadMultiple($entity_ids);
        $total = 0;
        /** @var Node $comment */
        foreach ($comments as $comment) {
          $total += $comment->field_tk_amount->value;
        }
        $month_data[] = \Drupal::service('se_timekeeping.time_format')->formatHours($total);
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

    $build['user_timekeeping_statistics'] = [
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
      '#id' => 'user_timekeeping_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    return $build;
  }
}
