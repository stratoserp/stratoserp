<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Stock item permission tests.
 *
 * @covers \Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class StockItemPermissionsTest extends FunctionalTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/item/add/se_stock',
    '/se/items',
  ];

  /**
   * Test the stock item permissions.
   */
  public function testStockItemPermissions(): void {

    $this->basicPermissionCheck($this->pages);
  }

}
