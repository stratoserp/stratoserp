<?php

namespace Drupal\se_ticket\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Ticket statistics user" block.
 * @Block(
 *   id = "ticket_statistics_user",
 *   admin_label = @Translation("Ticket statistics per user"),
 * )
 */
class UserStatistics extends BlockBase {

  use ReportUtilityTrait;

  public function build() {
    $datasets = [];

    /** @var EntityInterface $node */
    $entity = $this->get_current_controller_entity();
    if ($entity->getEntityTypeId() !== 'user') {
      return [];
    }

    for ($i = 5; $i >= 0 ; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      $bg_colors = [];
      [$fg_color, $bg_color] = $this->generateColors();

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'se_ticket');
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $month_data[] = count($entity_ids);
        $fg_colors[] = $fg_color;
        $bg_colors[] = $bg_color;
      }

      $datasets[] = [
        'label' => $year,
        'data' => $month_data,
        'backgroundColor' => $fg_colors,
        'borderColor' => $fg_colors,
        'pointBackgroundColor' => $fg_colors,
        'hoverBackgroundColor' => $bg_colors,
        'fill' => FALSE,
      ];
    }

    $build['ticket_statistics_user'] = [
      '#data' => [
        'labels' => array_keys($this->reportingMonths()),
        'datasets' => $datasets,
      ],
      '#graph_type' => 'line',
      '#options' => [
        'fill' => FALSE,
        'tooltips' => [
          'mode' => 'point'
        ],
      ],
      '#id' => 'ticket_statistics_user',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    return $build;
  }
}
