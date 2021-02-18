<?php

declare(strict_types=1);

namespace Drupal\Tests\se_goods_receipt\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Goods receipt permission tests.
 *
 * @coversDefault Drupal\se_goods_receipt
 * @group se_goods_receipt
 * @group stratoserp
 */
class GoodsReceiptPermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/node/add/se_goods_receipt',
    '/se/goods-receipt-list',
  ];

  /**
   * Test the goods receipt permissions.
   */
  public function testGoodsReceiptPermissions(): void {

    $this->basicPermissionCheck($this->pages);
  }

}
