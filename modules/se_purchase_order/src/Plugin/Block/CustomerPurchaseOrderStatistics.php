<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\stratoserp\ErpCore;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Customer purchase order statistics" block.
 *
 * @Block(
 *   id = "customer_purchase_order_statistics",
 *   admin_label = @Translation("Customer purchase order statistics"),
 * )
 */
class CustomerPurchaseOrderStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * Customer purchase order statics block builder.
   */
  public function build() {
    $content = FALSE;
    $datasets = [];
    // @todo Move this to a service and pass in this.
    $type = 'se_purchase_order';
    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$type];

    /** @var \Drupal\Core\Entity\EntityInterface $node */
    if (!$node = $this->getCurrentControllerEntity()) {
      return [];
    }

    // This is designed to run only for monthly.
    if ($node->bundle() !== 'se_customer') {
      return [];
    }

    for ($i = 5; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('node');
        $query->condition('type', $bundleFieldType);
        $query->condition('se_bu_ref', $node->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($entity_ids);
        if ($nodes && count($nodes) > 0) {
          $content = TRUE;
        }

        $total = 0;
        $bundle_field_total = 'se_' . $bundleFieldType . '_total';
        /** @var \Drupal\node\Entity\Node $node */
        foreach ($nodes as $node) {
          $total += $node->{$bundle_field_total}->value;
        }
        $month_data[] = $total;
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
      ];
    }

    if (!$content) {
      return [];
    }

    $build['customer_' . $type . '_statistics'] = [
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
      '#id' => 'customer_' . $type . '_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
