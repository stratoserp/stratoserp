<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Service;

/**
 * Provide functions to format currency values.
 *
 * @package Drupal\se_accounting\Service
 */
class CurrencyFormat implements CurrencyFormatInterface {

  /**
   * {@inheritdoc}
   */
  public function formatDisplay(int $value): string {

    // Don't try and divide by zero.
    if (!empty($value)) {

      // Format with thousands and decimals.
      return (string) number_format($value / 100, 2);
    }

    return '0';
  }

  /**
   * {@inheritdoc}
   */
  public function formatStorage(string $value): string {

    // Remove thousands separator.
    $value = str_replace(['$', ','], '', $value);

    // Multiply by 100 to change to cents, dont bother with zero.
    if (!empty($value)) {
      return (string) ((float) $value * 100);
    }

    return '0';
  }

  /**
   * {@inheritdoc}
   */
  public function formatRaw(int $value): string {

    // Don't try and divide by zero.
    if (!empty($value)) {

      // Format with thousands and decimals.
      return sprintf('%0.2f', $value / 100);
    }

    return '0';
  }

}
