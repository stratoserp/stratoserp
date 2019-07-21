<?php

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_item\Functional\ItemTestBase;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 *
 */
class StockItemCrudTest extends ItemTestBase {

  protected $staff;

  public function testStockItemAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $item = $this->addStockItem();

    $this->drupalLogout();
  }

}