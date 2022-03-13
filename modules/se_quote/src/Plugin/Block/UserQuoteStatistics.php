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

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if (!$entity = $this->getCurrentControllerEntity()) {
      return [];
    }

    if (!isset($entity) || $entity->getEntityTypeId() !== 'user') {
      $user_id = \Drupal::currentUser()->id();
      $entity = \Drupal::entityTypeManager()->getStorage('user')->load($user_id);
    }

    for ($i = 5; $i >= 0; $i--) {
      $year = date('Y') - $i;
      $month_data = [];
      $fg_colors = [];
      [$fg_color] = $this->generateColorsDarkening(100, NULL, 50);

      foreach ($this->reportingMonths($year) as $month => $timestamps) {
        $query = \Drupal::entityQuery('se_quote');
        $query->condition('uid', $entity->id());
        $query->condition('created', $timestamps['start'], '>=');
        $query->condition('created', $timestamps['end'], '<');
        $entity_ids = $query->execute();
        $quotes = \Drupal::entityTypeManager()
          ->getStorage('se_quote')
          ->loadMultiple($entity_ids);

        $month = 0;
        /** @var \Drupal\se_quote\Entity\Quote $quote */
        foreach ($quotes as $quote) {
          $month += $quote->se_total->value;
        }
        $month_data[] = $month;
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

    $build['user_quote_statistics'] = [
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
      '#id' => 'user_quote_statistics',
      '#type' => 'chartjs_api',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
