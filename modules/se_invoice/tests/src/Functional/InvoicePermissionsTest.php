<?php

declare(strict_types=1);

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test invoice permissions.
 *
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoicePermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for customer.
   *
   * @var \Faker\Factory
   */
  protected $customer;

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private $pages = [
    '/invoice/add',
    '/se/customers/invoice-list',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testInvoicePermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
