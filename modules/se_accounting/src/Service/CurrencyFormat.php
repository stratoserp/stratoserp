<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Service;

/**
 * Class CurrencyFormat.
 *
 * @package Drupal\se_accounting\Service
 */
class CurrencyFormat {

  /**
   * Convert cents string from storage to human readable currency amount.
   *
   * @param int $value
   *   The value in cents to be formatted.
   *
   * @return string
   *   The formatted value
   */
  public function formatDisplay($value): string {

    // Don't try and divide by zero.
    if (!empty($value)) {

      // Format with thousands and decimals.
      return (string) number_format($value / 100, 2);
    }

    return '0';
  }

  /**
   * Convert from human readable currency amount to storage cents string.
   *
   * @param string $value
   *   The formatted value to be converted.
   *
   * @return string
   *   The string value to store.
   */
  public function formatStorage(string $value): string {

    // Remove thousands separator.
    $value = str_replace(['$', ','], '', $value);

    // Multiply by 100 to change to cents, dont bother with zero.
    if (!empty($value)) {
      return (string) ((float) $value * 100);
    }

    return '0';
  }

  /**
   * Convert a cents string from storage to a raw format for graphing.
   *
   * @param int $value
   *   The value in cents to be formatted.
   *
   * @return string
   *   The formatted value.
   */
  public function formatRaw(int $value): string {

    // Don't try and divide by zero.
    if (!empty($value)) {

      // Format with thousands and decimals.
      return sprintf('%0.2f', $value / 100);
    }

    return '0';
  }

}
