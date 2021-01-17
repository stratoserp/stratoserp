<?php

declare(strict_types=1);

namespace Drupal\Tests\se_stock\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test for adding stock.
 *
 * @coversDefault Drupal\se_stock
 * @group se_item
 * @group se_stock
 * @group stratoserp
 */
class StockItemCrudTest extends FunctionalTestBase {

  use ItemTestTrait;

  /**
   * Storage for the staff user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $staff;

  /**
   * Test basic adding a stock item.
   */
  public function testAddStockItem(): void {

    $this->staff = $this->setupStaffUser();

    $this->drupalLogin($this->staff);

    $this->addStockItem();

    $this->drupalLogout();
  }

}
