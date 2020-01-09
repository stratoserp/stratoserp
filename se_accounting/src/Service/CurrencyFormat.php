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
   * Convert a cents string from storage to a human readable float style currency amount.
   *
   * @param int $value
   *
   * @return string
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
   * Convert a float style human readable currency amount to an cents and string for storage.
   *
   * @param string $value
   *
   * @return string
   */
  public function formatStorage(string $value): string {

    // Remove thousands separator.
    $value = str_replace(',', '', $value);

    // Multiply by 100 to change to cents, dont bother with zero
    // Don't try and divide by zero.
    if (!empty($value)) {
      return (string) ((float)$value * 100);
    }

    return '0';
  }

  /**
   * Convert a cents string from storage to a raw format for graphing.
   *
   * @param int $value
   *
   * @return string
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
