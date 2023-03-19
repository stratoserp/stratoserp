<?php

declare(strict_types=1);

namespace Drupal\se_quote\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 * Provides a "User quote statistics" block.
 *
 * @Block(
 *   id = "user_quote_statistics",
 *   admin_label = @Translation("User quote statistics"),
 * )
 */
class UserQuoteStatistics extends BlockBase {

  use ReportUtilityTrait;

  /**
   * User quote statistics block builder.
   */
  public function build() {
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
        $query = \Drupal::entityQuery('se_quote');
        $query->accessCheck(TRUE);
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $quotes = \Drupal::entityTypeManager()
          ->getStorage('se_quote')
          ->loadMultiple($entity_ids);

        /** @var \Drupal\se_quote\Entity\Quote $quote */
        foreach ($quotes as $quote) {
          $month += $quote->getTotal();
        }

        $month_data[] = $month ?: '';
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

    $build['user_quote_statistics'] = [
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
      '#id' => 'user_quote_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
