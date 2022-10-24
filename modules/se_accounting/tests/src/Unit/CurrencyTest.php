<?php

declare(strict_types=1);

namespace Drupal\Tests\se_accounting\Unit;

use Drupal\se_accounting\Service\CurrencyFormatService;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the currency storage, display and raw conversion.
 *
 * @covers \Drupal\se_accounting\Service\CurrencyFormatService
 * @group se_accounting
 * @group stratoserp
 */
class CurrencyTest extends UnitTestCase {

  /**
   * Service holder var.
   *
   * @var \Drupal\se_accounting\Service\CurrencyFormatService
   */
  protected CurrencyFormatService $currencyService;

  /**
   * Setup the service holder var.
   */
  protected function setup(): void {
    $this->currencyService = new CurrencyFormatService();
  }

  /**
   * Tests CurrencyStorage.
   *
   * @param string $input
   *   Input strings.
   * @param string $expected
   *   Expected output.
   *
   * @dataProvider currencyStorageProvider
   */
  public function testCurrencyStorage(string $input, string $expected): void {
    self::assertEquals($expected, $this->currencyService->formatStorage($input));
  }

  /**
   * Tests CurrencyDisplay.
   *
   * @param int $input
   *   Input strings.
   * @param string $expected
   *   Expected output.
   *
   * @dataProvider currencyDisplayProvider
   */
  public function testCurrencyDisplay(int $input, string $expected): void {
    self::assertEquals($expected, $this->currencyService->formatDisplay($input));
  }

  /**
   * Tests CurrencyRaw.
   *
   * @param int $input
   *   Input strings.
   * @param string $expected
   *   Expected output.
   *
   * @dataProvider currencyRawProvider
   */
  public function testCurrencyRaw(int $input, string $expected): void {
    self::assertEquals($expected, $this->currencyService->formatRaw($input));
  }

  /**
   * Data provider for testCurrencyStorage.
   */
  public function currencyStorageProvider(): array {
    return [
      ['1', '100'],
      ['1,000.00', '100000'],
      ['1,234.56', '123456'],
    ];
  }

  /**
   * Data provider for testCurrencyDisplay.
   */
  public function currencyDisplayProvider(): array {
    return [
      [100, '1.00'],
      [100000, '1,000.00'],
      [123456, '1,234.56'],
    ];
  }

  /**
   * Data provider for testCurrencyDisplay.
   */
  public function currencyRawProvider(): array {
    return [
      [100, '1.00'],
      [100000, '1000.00'],
      [123456, '1234.56'],
    ];
  }

}
