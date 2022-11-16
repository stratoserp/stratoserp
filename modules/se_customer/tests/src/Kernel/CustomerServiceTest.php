<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\se_customer\Entity\Customer;

/**
 * Kernel Test for the customer service.
 *
 * @covers \Drupal\se_customer\Service\CustomerService::getInvoiceDayTimestamp
 * @group se_customer
 * @group stratoserp
 */
class CustomerServiceTest extends KernelTestBase {

  /**
   * The modules to be enable.
   *
   * @var string[]
   */
  protected static $modules = [
    'se_customer',
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
    $this->installEntitySchema('se_customer');
    $this->installConfig(['system', 'user', 'taxonomy', 'se_customer']);
  }

  /**
   * Tests the invoice day timestamp service.
   *
   * @dataProvider invoiceDayStampDataProvider
   */
  public function testInvoiceDayStamp($input, $output) {

    $customer = Customer::create([
      'type' => 'se_customer',
      'name' => 'Test customer - ' . $input,
      'se_invoice_day' => $input,
    ]);
    $customer->save();

    $timestamp = \Drupal::service('se_customer.service')->getInvoiceDayTimestamp($customer);

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
