<?php

declare(strict_types=1);

namespace Drupal\Tests\se_store\Functional;

/**
 * Test Store Permiossions.
 *
 * @covers \Drupal\se_store
 * @group se_store
 * @group stratoserp
 */
class StorePermissionsTest extends StoreTestBase {

  /**
   * List of pages to check permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/store/add',
    '/se/customers/store-list',
  ];

  /**
   * Test the owner store permissions for the default pages.
   */
  public function testStorePermissions(): void {
    $this->ownerPermissionCheck($this->pages);
  }

}
