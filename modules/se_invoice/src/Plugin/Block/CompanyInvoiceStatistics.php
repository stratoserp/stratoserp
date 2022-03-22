<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Company invoice statistics" block.
 *
 * @Block(
 *   id = "company_invoice_statistics",
 *   admin_label = @Translation("Company invoice statistics"),
 * )
 */
class CompanyInvoiceStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * Company invoice statistics block builder.
   */
  public function build() {
    $content = FALSE;
    $datasets = [];

    $config = \Drupal::service('config.factory')->get('stratoserp.settings');

    $timeframe = $config->get('statistics_timeframe') ?: 1;
    for ($i = $timeframe; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingPeriods($year) as $timestamps) {
        $query = \Drupal::entityQuery('se_invoice');
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();

        $invoices = \Drupal::entityTypeManager()
          ->getStorage('se_invoice')
          ->loadMultiple($entity_ids);
        $total = 0;
        if (count($invoices)) {
          $content = TRUE;
        }
        /** @var \Drupal\Core\Entity\EntityInterface $invoice */
        foreach ($invoices as $invoice) {
          if (is_object($invoice) && $invoice->hasField('se_total')) {
            $total += $invoice->se_total->value;
          }
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

    $build['company_invoice_statistics'] = [
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
      '#id' => 'company_invoice_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
