<?php

declare(strict_types=1);

namespace Drupal\Tests\se_goods_receipt\Functional;

/**
 * Goods receipt permission tests.
 *
 * @coversDefault Drupal\se_goods_receipt
 * @group se_goods_receipt
 * @group stratoserp
 */
class GoodsReceiptPermissionsTest extends GoodsReceiptTestBase {

  /**
   * Faker factory for Goods reeipt.
   *
   * @var \Faker\Factory
   */
  protected $goodsReceipt;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/goods-receipt/add',
    '/se/customers/goods-receipt-list',
    '/se/suppliers/goods-receipt-list',
  ];

  /**
   * Test the goods receipt permissions.
   */
  public function testGoodsReceiptPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
