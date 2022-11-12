<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Service;

/**
 * Interface definitions for the tax amount service.
 */
interface TaxAmountServiceInterface {

  /**
   * Extract the tax amount from a total amount.
   *
   * Simple 10% tax for Australia.
   *
   * @param int $value
   *   The total value.
   *
   * @return string
   *   The formatted tax amount.
   */
  public function calculateTax(int $value): string;

}
