<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Stock item permission tests.
 *
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class StockItemPermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

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
    '/item/add/se_stock',
    '/se/item-list',
  ];

  /**
   * Test the stock item permissions.
   */
  public function testStockItemPermissions(): void {

    $this->basicPermissionCheck($this->pages);
  }

}
