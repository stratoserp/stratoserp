<?php

declare(strict_types=1);

namespace Drupal\Tests\se_untracked_stock\Functional;

use Drupal\Tests\se_item\Functional\ItemTestBase;

/**
 * Test for adding stock.
 *
 * @coversDefault Drupal\se_untracked_stock
 * @group se_item
 * @group se_untracked_stock
 * @group stratoserp
 */
class UntrackedStockItemCrudTest extends ItemTestBase {

  /**
   * Test basic adding a stock item.
   */
  public function testAddStockItem(): void {
    $this->drupalLogin($this->staff);
    $this->addUntrackedStockItem();
    $this->drupalLogout();
  }

}
