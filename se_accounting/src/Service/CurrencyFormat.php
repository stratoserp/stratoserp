<?php

namespace Drupal\se_accounting\Service;

class CurrencyFormat {

  public function formatDisplay(string $value) {
    // Format the stored value for display.
    return sprintf("%-1.2f", $value / 100);
  }

  public function formatStorage(float $value) {
    // Format the displayed value for storage.
    return (int)$value * 100;
  }

}