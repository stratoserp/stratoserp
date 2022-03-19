<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "User ticket statistics" block.
 *
 * @Block(
 *   id = "user_ticket_statistics",
 *   admin_label = @Translation("User ticket statistics"),
 * )
 */
class UserTicketStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * User ticket statistics block builder.
   */
  public function build() {
    $datasets = [];

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getCurrentControllerEntity();
    if (!isset($entity) || $entity->getEntityTypeId() !== 'user') {
      $user_id = \Drupal::currentUser()->id();
      $entity = \Drupal::entityTypeManager()->getStorage('user')->load($user_id);
    }

    for ($i = 1; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        if (!$timestamps['start']) {
          continue;
        }

        $query = \Drupal::entityQuery('se_ticket');
        $query->condition('user_id', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();

        $month_data[] = count($entity_ids) ?? '';
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

    $build['user_ticket_statistics'] = [
      '#data' => [
        'labels' => array_keys($this->reportingMonths()),
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
      '#id' => 'user_ticket_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
