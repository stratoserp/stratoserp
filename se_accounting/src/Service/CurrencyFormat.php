<?php

namespace Drupal\se_accounting\Service;

class CurrencyFormat {

  public function formatDollars(string $value) {
    // Convert cents into dollars and cents as humans expect.
    return sprintf("%-1.2f", $value / 100);
  }

}