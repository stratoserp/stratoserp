<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Service;

/**
 * Time formatting service to switch between types.
 *
 * @package Drupal\se_timekeeping\Service
 */
interface TimeFormatInterface {

  /**
   * Given minutes, return a nicely formatted string of hours:minutes.
   *
   * @param float $value
   *   Integer number of hours.
   *
   * @return string
   *   The formatted string.
   */
  public function formatHours($value): string;

  /**
   * Given minutes, return a float of hours.minutes.
   *
   * @param float $value
   *   Integer number of hours.
   *
   * @return float
   *   The float number.
   */
  public function formatDecimal($value): float;

}
