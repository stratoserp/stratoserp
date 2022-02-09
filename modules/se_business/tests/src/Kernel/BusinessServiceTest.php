<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\se_business\Entity\Business;

/**
 * Kernel Test for the business service.
 */
class BusinessServiceTest extends KernelTestBase {

  public static $modules = [
    'se_business',
    'se_accounting',
    'stratoserp',
    'link',
    'datetime',
    'geolocation',
    'taxonomy',
    'filter',
    'user',
    'options',
    'text',
    'telephone',
    'field',
    'system',
  ];

  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('se_business');
    $this->installConfig(['system', 'user', 'taxonomy', 'se_business']);
  }

  /**
   * Tests the invoice day timestamp service.
   *
   * @dataProvider invoiceDayStampDataProvider
   */
  public function testInvoiceDayStamp($input, $output) {

    $business = Business::create([
      'type' => 'se_business',
      'name' => 'Test business - ' . $input,
      'se_invoice_day' => $input,
    ]);
    $business->save();

    $timestamp = \Drupal::service('se_business.service')->getInvoiceDayTimestamp($business);

    $this->assertEquals(date('Y-m-d', $timestamp) . ' 00:00:00', $output);
  }

  /**
   * Data provider for the invoice day stamp test.
   *
   * @return array
   *   Contains dates and expected dates.
   */
  public function invoiceDayStampDataProvider() {
    $info[] = [
      1, date('Y-m-') . '01 00:00:00',
    ];

    $info[] = [
      7, date('Y-m-') . '07 00:00:00',
    ];

    $info[] = [
      15, date('Y-m-') . '15 00:00:00',
    ];

    return $info;
  }

}
