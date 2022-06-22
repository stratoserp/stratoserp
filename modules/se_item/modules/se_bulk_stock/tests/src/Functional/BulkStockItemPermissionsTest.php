<?php

declare(strict_types=1);

namespace Drupal\Tests\se_bulk_stock\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Stock item permission tests.
 *
 * @coversDefault Drupal\se_bulk_stock
 * @group se_bulk_stock
 * @group stratoserp
 */
class BulkStockItemPermissionsTest extends FunctionalTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/item/add/se_bulk_stock',
    '/se/items',
  ];

  /**
   * Test the stock item permissions.
   */
  public function testBulkStockItemPermissions(): void {

    $this->basicPermissionCheck($this->pages);
  }

}
