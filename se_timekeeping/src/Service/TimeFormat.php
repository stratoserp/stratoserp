<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Service;

/**
 * Class TimeFormat.
 *
 * @package Drupal\se_timekeeping\Service
 */
class TimeFormat {

  /**
   * Given minutes, return a nicely formatted string of hours:minutes.
   *
   * @param float $value
   *   Integer number of hours.
   *
   * @return string
   *   The formatted string.
   */
  public function formatHours($value): string {
    $hours = (int) ($value / 60);
    $minutes = $value % 60;
    return sprintf('%1s:%02s', $hours, $minutes);
  }

  /**
   * Given minutes, return a float of hours.minutes.
   *
   * @param float $value
   *   Integer number of hours.
   *
   * @return float
   *   The float number.
   */
  public function formatDecimal($value): float {
    $hours = (int) ($value / 60);
    $minutes = $value % 60;
    return (float) sprintf('%1s.%02s', $hours, $minutes);
  }

}
