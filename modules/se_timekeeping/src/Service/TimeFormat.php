<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Service;

/**
 * Time formatting service to switch between types.
 *
 * @package Drupal\se_timekeeping\Service
 */
class TimeFormat implements TimeFormatInterface {

  /**
   * {@inheritdoc}
   */
  public function formatHours($value): string {
    $hours = (int) ($value / 60);
    $minutes = $value % 60;
    return sprintf('%1s:%02s', $hours, $minutes);
  }

  /**
   * {@inheritdoc}
   */
  public function formatDecimal($value): float {
    $hours = (int) ($value / 60);
    $minutes = $value % 60;
    return (float) sprintf('%1s.%02s', $hours, $minutes);
  }

}
