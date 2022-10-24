<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Service;

interface CurrencyFormatServiceInterface {

  /**
   * Convert cents string from storage to human readable currency amount.
   *
   * @param int $value
   *   The value in cents to be formatted.
   *
   * @return string
   *   The formatted value
   */
  public function formatDisplay(int $value): string;

  /**
   * Convert from human-readable currency amount to storage cents string.
   *
   * @param string $value
   *   The formatted value to be converted.
   *
   * @return string
   *   The string value to store.
   */
  public function formatStorage(string $value): string;

  /**
   * Convert a cents string from storage to a raw format for graphing.
   *
   * @param int $value
   *   The value in cents to be formatted.
   *
   * @return string
   *   The formatted value.
   */
  public function formatRaw(int $value): string;

}
