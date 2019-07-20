<?php

namespace Drupal\se_purchase_order\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\se_core\ErpCore;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "User purchase order statistics" block.
 * @Block(
 *   id = "user_purchase_order_statistics",
 *   admin_label = @Translation("User purchase order statistics"),
 * )
 */
class UserPurchaseOrderStatistics extends BlockBase {

  use ReportUtilityTrait;

  public function build() {
    $content = FALSE;
    $datasets = [];
    // TODO Move this to a service and pass in this.
    $type = 'se_purchase_order';
    $bundle_field_type = 'field_' . ErpCore::ITEMS_BUNDLE_MAP[$type];

    /** @var EntityInterface $node */
    if (!$entity = $this->get_current_controller_entity()) {
      return [];
    }

    // This is designed to run only for users.
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
        $query->condition('type', $type);
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($entity_ids);
        if ($nodes && count($nodes) > 0) {
          $content = TRUE;
        }

        $month = 0;
        /** @var Node $node */
        foreach ($nodes as $node) {
          $month += $node->{$bundle_field_type . '_total'}->value;
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
          'mode' => 'dataset'
        ],
        'pointRadius' => 5,
        'pointHoverRadius' => 10,
      ];
    }

    if (!$content) {
      return [];
    }

    $build['user_' . $type .'_statistics'] = [
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
      '#id' => 'user_' . $type . '_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    return $build;
  }
}
