<?php

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_testing\Functional\StockItemTestBase;

/**
 * @coversDefault Drupal\se_stock
 * @group se_item
 * @group stratoserp
 *
 */
class StockItemCrudTest extends StockItemTestBase {

  protected $staff;

  public function testStockItemAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $item = $this->addStockItem();

    $this->drupalLogout();
  }

}