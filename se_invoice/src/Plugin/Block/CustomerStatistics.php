<?php

namespace Drupal\se_invoice\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "Invoice statistics customer" block.
 * @Block(
 *   id = "invoice_statistics_customer",
 *   admin_label = @Translation("Invoice statistics per customer"),
 * )
 */
class CustomerStatistics extends BlockBase {

  use ReportUtilityTrait;

  public function build() {
    $datasets = [];

    /** @var EntityInterface $node */
    $node = $this->get_current_controller_entity();
    if ($node->bundle() !== 'se_customer') {
      return [];
    }

    for ($i = 5; $i >= 0 ; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      $bg_colors = [];
      [$fg_color, $bg_color] = $this->generateColors(50 + ($i * 20));

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'se_invoice');
        $query->condition('field_bu_ref', $node->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $invoices = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($entity_ids);
        $total = 0;
        /** @var Node $invoice */
        foreach ($invoices as $invoice) {
          $total += $invoice->field_in_total->value;
        }
        $month_data[] = $total;
        $fg_colors[] = $fg_color;
        $bg_colors[] = $bg_color;
      }

      $datasets[] = [
        'label' => $year,
        'data' => $month_data,
        'backgroundColor' => $fg_colors,
        'borderColor' => $fg_colors,
        'pointBackgroundColor' => $fg_colors,
        'hoverBackgroundColor' => $bg_colors,
        'fill' => FALSE,
      ];
    }

    $build['invoice_statistics_customer'] = [
      '#data' => [
        'labels' => array_keys($this->reportingMonths()),
        'datasets' => $datasets,
      ],
      '#graph_type' => 'line',
      '#options' => [
        'fill' => FALSE,
      ],
      '#id' => 'invoice_statistics_customer',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    return $build;
  }
}
