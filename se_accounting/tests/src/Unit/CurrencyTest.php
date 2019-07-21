<?php

namespace Drupal\Tests\se_accounting\Unit;

use Drupal\se_accounting\Service\CurrencyFormat;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefault Drupal\se_accounting
 * @group se_accounting
 * @group stratoserp
 */
class CurrencyTest extends UnitTestCase {

  /**
   * @var \Drupal\se_accounting\Service\CurrencyFormatclass
   */
  protected $currencyFormatService;

  /**
   *
   */
  protected function setup() {
    $this->currencyFormatService = new CurrencyFormat();
  }

  /**
   * Tests CurrencyStorage.
   *
   * @dataProvider currencyStorageProvider
   *
   * @param $input
   * @param $expected
   */
  public function testCurrencyStorage($input, $expected) {
    $this->assertEquals($expected, $this->currencyFormatService->formatStorage($input));
  }

  /**
   * Tests CurrencyDisplay.
   *
   * @dataProvider currencyDisplayProvider
   *
   * @param $input
   * @param $expected
   */
  public function testCurrencyDisplay($input, $expected) {
    $this->assertEquals($expected, $this->currencyFormatService->formatDisplay($input));
  }

  /**
   * Tests CurrencyRaw.
   *
   * @dataProvider currencyRawProvider
   *
   * @param $input
   * @param $expected
   */
  public function testCurrencyRaw($input, $expected) {
    $this->assertEquals($expected, $this->currencyFormatService->formatRaw($input));
  }

  /**
   * Data provider for testCurrencyStorage.
   */
  public function currencyStorageProvider() {
    return [
      [1, '100'],
      ['1,000.00', 100000],
      ['1,234.56', 123456],
    ];
  }

  /**
   * Data provider for testCurrencyDisplay.
   */
  public function currencyDisplayProvider() {
    return [
      [100, '1.00'],
      [100000, '1,000.00'],
      [123456, '1,234.56'],
    ];
  }

  /**
   * Data provider for testCurrencyDisplay.
   */
  public function currencyRawProvider() {
    return [
      [100, '1.00'],
      [100000, '1000.00'],
      [123456, '1234.56'],
    ];
  }

}
