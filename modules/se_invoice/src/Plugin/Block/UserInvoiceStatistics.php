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

    $config = \Drupal::service('config.factory')->get('stratoserp.settings');

    if (!$config->get('statistics_display')) {
      return [];
    }

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getCurrentControllerEntity();
    if (!isset($entity) || $entity->getEntityTypeId() !== 'user') {
      $uid = \Drupal::currentUser()->id();
      $entity = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    }

    $timeframe = $config->get('statistics_timeframe') ?: 1;
    for ($i = $timeframe; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingPeriods($year) as $timestamps) {
        $month = 0;
        if (!$timestamps['start']) {
          continue;
        }
        $entity_ids = \Drupal::entityQuery('se_invoice')
          ->accessCheck(TRUE)
          ->condition('uid', $entity->id())
          ->condition('created', $timestamps['start'], '>=')
          ->condition('created', $timestamps['end'], '<')
          ->execute();

        $invoices = \Drupal::entityTypeManager()
          ->getStorage('se_invoice')
          ->loadMultiple($entity_ids);
        if ($invoices && count($invoices) > 0) {
          $content = TRUE;
        }

        if (count($invoices)) {
          $content = TRUE;
        }

        /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
        foreach ($invoices as $invoice) {
          $month += $invoice->getTotal();
        }

        if ($month > 0) {
          $month_data[] = \Drupal::service('se_accounting.currency_format')->formatRaw((int) ($month ?? 0));
        }
        else {
          $month_data[] = '';
        }
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

    $build['user_invoice_statistics'] = [
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
      '#id' => 'user_invoice_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
