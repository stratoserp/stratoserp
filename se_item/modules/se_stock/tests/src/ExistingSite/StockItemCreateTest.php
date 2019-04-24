<?php

namespace Drupal\Tests\se_stock\ExistingSite;

/**
 * @coversDefault Drupal\se_stock
 * @group se_item
 * @group stratoserp
 *
 */
class StockItemCreateTest extends StockItemTestBase {

  protected $staff;

  public function testStockItemCreate() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    //$item = $this->addStockItem();

    $this->drupalLogout();
  }
}