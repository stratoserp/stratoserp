<?php

namespace Drupal\se_accounting\Service;

class CurrencyFormat {

  /**
   * Convert an int from storage to a human readable currency amount.
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
   * Convert a float human readable currency amount to an int for storage.
   *
   * @param float $value
   *
   * @return int
   */
  public function formatStorage(string $value) {

    // Remove thousands separator.
    $value = str_replace(',', '', $value);

    // Multiply by 100 to change to cents.
    return (string)($value * 100);
  }

}