<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Plugin\Block;

use Drupal\Core\Block\BlockBase;
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
    // @todo Move this to a service and pass in the entity type and customer.
    $type = 'se_purchase_order';

    $config = \Drupal::service('config.factory')->get('stratoserp.settings');

    if (!$config->get('statistics_display')) {
      return [];
    }

    /** @var \Drupal\Core\Entity\EntityInterface $customer */
    if (!$customer = $this->getCurrentControllerEntity()) {
      return [];
    }

    // This is designed to run only for customers.
    if ($customer->bundle() !== 'se_customer') {
      return [];
    }

    $timeframe = $config->get('statistics_timeframe') ?: 1;
    for ($i = $timeframe; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingPeriods($year) as $timestamps) {
        $query = \Drupal::entityQuery('se_purchase_order');
        $query->condition('se_cu_ref', $customer->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();

        $entities = \Drupal::entityTypeManager()
          ->getStorage($type)
          ->loadMultiple($entity_ids);
        if ($entities && count($entities) > 0) {
          $content = TRUE;
        }

        $total = 0;
        foreach ($entities as $entity) {
          $total += $entity->se_total->value;
        }
        $month_data[] = \Drupal::service('se_accounting.currency_format')->formatRaw($total ?? 0);
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

    $build['customer_po_statistics'] = [
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
      '#id' => 'customer_po_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
