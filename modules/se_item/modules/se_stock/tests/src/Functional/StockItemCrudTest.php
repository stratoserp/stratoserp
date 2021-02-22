<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Tests\se_item\Functional\ItemTestBase;

/**
 * Test for adding stock.
 *
 * @coversDefault Drupal\se_stock
 * @group se_item
 * @group se_stock
 * @group stratoserp
 */
class StockItemCrudTest extends ItemTestBase {

  /**
   * Test basic adding a stock item.
   */
  public function testAddStockItem(): void {
    $this->drupalLogin($this->staff);
    $this->addStockItem();
    $this->drupalLogout();
  }

}
