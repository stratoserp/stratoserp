<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Invoice statistics user" block.
 *
 * @Block(
 *   id = "user_invoice_statistics",
 *   admin_label = @Translation("User invoice statistics"),
 * )
 */
class UserInvoiceStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * User invoice statistics block builder.
   */
  public function build() {
    $content = FALSE;
    $datasets = [];

    /** @var \Drupal\Core\Entity\EntityInterface $node */
    if (!$entity = $this->getCurrentControllerEntity()) {
      return [];
    }

    if ($entity->getEntityTypeId() !== 'user') {
      return [];
    }

    $total = [];
    for ($i = 5; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'se_invoice');
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $invoices = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($entity_ids);
        if ($invoices && count($invoices) > 0) {
          $content = TRUE;
        }

        $month = 0;
        if (count($invoices)) {
          $content = TRUE;
        }

        /** @var \Drupal\node\Entity\Node $invoice */
        foreach ($invoices as $invoice) {
          $month += $invoice->se_in_total->value;
        }
        $month_data[] = \Drupal::service('se_accounting.currency_format')->formatRaw((int) ($month ?? 0));
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

    $build['user_invoice_statistics'] = [
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
      '#id' => 'user_invoice_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
