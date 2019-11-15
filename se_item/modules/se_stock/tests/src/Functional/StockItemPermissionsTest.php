<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class StockItemPermissionsTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/item/add/se_stock',
    '/se/item-list',
  ];

  /**
   *
   */
  public function testStockItemPermissions(): void {

    $this->basicPermissionCheck($this->pages);
  }

}
