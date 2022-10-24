<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Service;

interface TaxAmountServiceInterface {

  /**
   * Extract the tax amount from a total amount.
   *
   * @param int $value;
   *   The total value.
   *
   * @return string
   *   The formatted tax amount.
   */
  public function calculateTax(int $value): string;

}
