<?php

declare(strict_types=1);

namespace Drupal\se_report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\se_report\ReportUtilityTrait;

/**
 *
 */
class OverviewPageController extends ControllerBase {
  use ReportUtilityTrait;

  /**
   *
   */
  protected function getModuleName() {
    return 'report_overview_page';
  }

  /**
   *
   */
  public function dashboard() {
    $monthly_statistics = $this->monthlyStatistics();

    return [
      '#markup' => render($monthly_statistics),
    ];
  }

  /**
   *
   */
  private function monthlyStatistics() {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('monthly_statistics', [])
      ->build()) {
      return [];
    }

    return $block;
  }

}
