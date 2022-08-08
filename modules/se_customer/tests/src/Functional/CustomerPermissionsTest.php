<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

/**
 * Test basic permissions.
 *
 * @covers \Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerPermissionsTest extends CustomerTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/customer/add',
    '/se/customers',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testCustomerPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
