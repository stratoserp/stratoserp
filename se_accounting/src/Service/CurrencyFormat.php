<?php

namespace Drupal\se_accounting\Service;

class CurrencyFormat {

  public function formatDollars(string $value) {
    return sprintf("%-1.2f", $value);
    // return sprintf("%-1.2f", $value / 100);
  }

}