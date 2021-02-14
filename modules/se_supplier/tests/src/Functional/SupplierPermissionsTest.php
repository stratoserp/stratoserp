<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test basic permissions.
 *
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierPermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for supplier.
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
    '/supplier/add',
    '/se/supplier-list',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testSupplierPermissions() {
    $this->basicPermissionCheck($this->pages);
  }

}
