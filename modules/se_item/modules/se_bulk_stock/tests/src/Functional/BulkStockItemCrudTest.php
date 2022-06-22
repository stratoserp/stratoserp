<?php

declare(strict_types=1);

namespace Drupal\Tests\se_bulk_stock\Functional;

use Drupal\Tests\se_item\Functional\ItemTestBase;

/**
 * Test for adding stock.
 *
 * @coversDefault Drupal\se_bulk_stock
 * @group se_item
 * @group se_bulk_stock
 * @group stratoserp
 */
class BulkStockItemCrudTest extends ItemTestBase {

  /**
   * Test basic adding a stock item.
   */
  public function testAddBulkStockItem(): void {
    $this->drupalLogin($this->staff);
    $this->addBulkStockItem();
    $this->drupalLogout();
  }

}
