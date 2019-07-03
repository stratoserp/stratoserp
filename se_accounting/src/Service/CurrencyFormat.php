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
    // Format the stored value for display.
    // Don't try and divide by zero
    if (!empty($value)) {
      return (float)sprintf('%-1.2f', $value / 100);
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
  public function formatStorage(float $value) {
    // Format the displayed value for storage.
    return (int)($value * 100);
  }

}