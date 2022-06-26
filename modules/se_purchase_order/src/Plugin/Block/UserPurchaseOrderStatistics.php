<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "User purchase order statistics" block.
 *
 * @Block(
 *   id = "user_purchase_order_statistics",
 *   admin_label = @Translation("User purchase order statistics"),
 * )
 */
class UserPurchaseOrderStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * User purchase order statistics block builder.
   */
  public function build() {
    $content = FALSE;
    $datasets = [];

    $config = \Drupal::service('config.factory')->get('stratoserp.settings');

    if (!$config->get('statistics_display')) {
      return [];
    }

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if (!$entity = $this->getCurrentControllerEntity()) {
      return [];
    }

    if (!isset($entity) || $entity->getEntityTypeId() !== 'user') {
      $user_id = \Drupal::currentUser()->id();
      $entity = \Drupal::entityTypeManager()->getStorage('user')->load($user_id);
    }

    $timeframe = $config->get('statistics_timeframe') ?: 1;
    for ($i = $timeframe; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingPeriods($year) as $timestamps) {
        $query = \Drupal::entityQuery('se_purchase_order');
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();

        $entities = \Drupal::entityTypeManager()
          ->getStorage('se_purchase_order')
          ->loadMultiple($entity_ids);
        if ($entities && count($entities) > 0) {
          $content = TRUE;
        }

        $month = 0;
        foreach ($entities as $entity) {
          $month += $entity->se_total->value;
        }
        $month_data[] = $month;
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

    if (!$content) {
      return [];
    }

    $build['user_po_statistics'] = [
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
      '#id' => 'user_po_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
