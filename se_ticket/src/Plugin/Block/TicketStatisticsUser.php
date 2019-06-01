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
class TicketStatisticsUser extends BlockBase {

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
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'se_ticket');
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $month_data[] = count($entity_ids);
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

    $build['ticket_statistics_user'] = [
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
      '#id' => 'ticket_statistics_user',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    return $build;
  }
}
