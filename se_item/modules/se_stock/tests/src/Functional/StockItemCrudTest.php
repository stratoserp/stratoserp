<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_item\Functional\ItemTestBase;
use Drupal\Tests\se_item\Traits\ItemCreationTestTrait;

/**
 * @coversDefault Drupal\se_stock
 * @group se_stock
 * @group stratoserp
 */
class StockItemCrudTest extends ItemTestBase {

  use ItemCreationTestTrait;

  protected $staff;

  /**
   *
   */
  public function testAddStockItem() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $item = $this->addStockItem();

    $this->drupalLogout();
  }

  private function addStockItem() {
    $this->createItemContent([
      'name' => $this->faker->name,

    ]);
  }

}
