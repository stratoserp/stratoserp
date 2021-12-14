<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\stratoserp\Constants;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Business purchase order statistics" block.
 *
 * @Block(
 *   id = "business_purchase_order_statistics",
 *   admin_label = @Translation("Business purchase order statistics"),
 * )
 */
class BusinessPurchaseOrderStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * Business purchase order statics block builder.
   */
  public function build() {
    $content = FALSE;
    $datasets = [];
    // @todo Move this to a service and pass in the entity type and business.
    $type = 'se_purchase_order';
    $bundleFieldType = Constants::SE_ITEM_LINE_BUNDLES[$type];

    /** @var \Drupal\Core\Entity\EntityInterface $business */
    if (!$business = $this->getCurrentControllerEntity()) {
      return [];
    }

    // This is designed to run only for businesses.
    if ($business->bundle() !== 'se_business') {
      return [];
    }

    for ($i = 5; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('se_purchase_order');
        $query->condition('se_bu_ref', $business->id());
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
        $bundle_field_total = 'se_' . $bundleFieldType . '_total';
        foreach ($entities as $entity) {
          $total += $entity->{$bundle_field_total}->value;
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
      ];
    }

    if (!$content) {
      return [];
    }

    $build['business_' . $bundleFieldType . '_statistics'] = [
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
      '#id' => 'business_' . $bundleFieldType . '_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
