<?php

namespace Drupal\se_accounting\Service;

/**
 * Class CurrencyFormat
 *
 * @package Drupal\se_accounting\Service
 */
class CurrencyFormat {

  /**
   * Convert an cents string from storage to a human readable float style currency amount.
   *
   * @param string $value
   *
   * @return string
   */
  public function formatDisplay(string $value) {

    // Don't try and divide by zero
    if (!empty($value)) {

      // Format with thousands and decimals.
      return (string)number_format($value / 100, 2);
    }

    return 0;
  }

  /**
   * Convert a float style human readable currency amount to an cents and string for storage.
   *
   * @param string $value
   *
   * @return string
   */
  public function formatStorage(string $value) {

    // Remove thousands separator.
    $value = str_replace(',', '', $value);

    // Multiply by 100 to change to cents, dont bother with zero
    // Don't try and divide by zero
    if (!empty($value)) {
      return (string) ($value * 100);
    }

    return 0;
  }

  /**
   * Convert an cents string from storage to a raw format for graphing.
   *
   * @param string $value
   *
   * @return string
   */
  public function formatRaw(string $value) {

    // Don't try and divide by zero
    if (!empty($value)) {

      // Format with thousands and decimals.
      return sprintf('%0.2f', $value / 100);
    }

    return 0;
  }

}