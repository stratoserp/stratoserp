<?php

declare(strict_types=1);

namespace Drupal\se_quote\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Customer quote statistics" block.
 *
 * @Block(
 *   id = "customer_quote_statistics",
 *   admin_label = @Translation("Customer quote statistics"),
 * )
 */
class CustomerQuoteStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * Customer quote statistics block builder.
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

    if ($entity->bundle() !== 'se_customer') {
      return [];
    }

    $timeframe = $config->get('statistics_timeframe') ?: 1;
    for ($i = $timeframe; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingPeriods($year) as $timestamps) {
        $query = \Drupal::entityQuery('se_quote');
        $query->condition('se_cu_ref', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $quotes = \Drupal::entityTypeManager()
          ->getStorage('se_quote')
          ->loadMultiple($entity_ids);
        $total = 0;
        if (count($quotes)) {
          $content = TRUE;
        }
        /** @var \Drupal\se_quote\Entity\Quote $quote */
        foreach ($quotes as $quote) {
          $total += $quote->getTotal();
        }
        $month_data[] = \Drupal::service('se_accounting.currency_format')->formatRaw((int) ($total ?? 0));
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

    $build['quote_statistics_customer'] = [
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
      '#id' => 'quote_statistics_customer',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
