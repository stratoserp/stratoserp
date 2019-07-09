<?php

namespace Drupal\Tests\se_stock\Functional;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 *
 */
class StockItemPermissionsTest extends ExistingSiteBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_invoice',
  ];

  public function testStockItemPermissions() {

    $this->permissionCheck($this->pages);
  }

}
