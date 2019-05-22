<?php

namespace Drupal\se_invoice\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "User invoice statistics customer" block.
 * @Block(
 *   id = "user_invoice_statistics_customer",
 *   admin_label = @Translation("User invoice statistics per customer"),
 * )
 */
class UserInvoiceStatistics extends BlockBase {

  use ReportUtilityTrait;

  public function build() {
    $datasets = [];

    /** @var EntityInterface $node */
    if (!$node = $this->get_current_controller_entity()) {
      return [];
    }

    if ($node->bundle() !== 'se_customer') {
      return [];
    }

    $total = [];
    for ($i = 5; $i >= 0 ; $i--) {
      $year = date('Y') - $i;

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
        /** @var Node $invoice */
        foreach ($invoices as $invoice) {
          if (isset($total[$invoice->getOwner()->name->value][$month])) {
            $total[$invoice->getOwner()->name->value][$month] += $invoice->field_in_total->value;
          }
          else {
            $total[$invoice->getOwner()->name->value][$month] = $invoice->field_in_total->value;
          }
        }
      }
    }

    foreach ($total as $user => $data) {
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);
      $fg_colors[] = $fg_color;
      foreach ($data as $month => $value) {
        $datasets[] = [
          'label' => $user,
          'stack' => $month,
          'data' => [$value],
          'backgroundColor' => $fg_colors,
          'borderColor' => $fg_colors,
          'hoverBackgroundColor' => $fg_colors,
          'fill' => FALSE,
          'hover' => [
            'mode' => 'dataset'
          ],
        ];
      }
    }

    $build['user_invoice_statistics_customer'] = [
      '#data' => [
        'labels' => array_keys($this->reportingMonths()),
        'datasets' => $datasets,
      ],
      '#graph_type' => 'bar',
      '#options' => [
        'tooltips' => [
          'mode' => 'point'
        ],
        'hover' => [
          'mode' => 'dataset'
        ],
        'scales' => [
          'yAxes' => [
            'stacked' => TRUE,
          ],
          'xAxes' => [
            'stacked' => TRUE,
          ],
        ]
      ],
      '#id' => 'user_invoice_statistics_customer',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    return $build;
  }
}
