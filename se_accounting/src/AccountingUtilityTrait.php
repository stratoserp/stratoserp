<?php

namespace Drupal\se_accounting;

trait AccountingUtilityTrait {
  public function currencyBare($value) {
    return sprintf("%-20.2f", $value);
  }

}