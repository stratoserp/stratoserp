<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Service;

/**
 * Provide functions to calculate tax amount from total.
 */
class TaxAmountService implements TaxAmountServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function calculateTax($value): string {
    return (string) number_format($value / 10, 2);
  }

}
