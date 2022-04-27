<?php

declare(strict_types=1);

namespace Drupal\Tests\se_untracked_stock\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Stock item permission tests.
 *
 * @coversDefault Drupal\se_untracked_stock
 * @group se_untracked_stock
 * @group stratoserp
 */
class UntrackedStockItemPermissionsTest extends FunctionalTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/item/add/se_untracked_stock',
    '/se/items',
  ];

  /**
   * Test the stock item permissions.
   */
  public function testStockItemPermissions(): void {

    $this->basicPermissionCheck($this->pages);
  }

}
