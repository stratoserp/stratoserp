<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

/**
 * Test basic permissions.
 *
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierPermissionsTest extends SupplierTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/supplier/add',
    '/se/suppliers',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testSupplierPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
