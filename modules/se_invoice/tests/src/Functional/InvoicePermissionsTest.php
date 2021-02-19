<?php

declare(strict_types=1);

namespace Drupal\Tests\se_invoice\Functional;

/**
 * Test invoice permissions.
 *
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoicePermissionsTest extends InvoiceTestBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $invoice;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/invoice/add',
    '/se/invoice-list',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testInvoicePermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
