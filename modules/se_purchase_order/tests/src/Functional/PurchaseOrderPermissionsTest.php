<?php

declare(strict_types=1);

namespace Drupal\Tests\se_purchase_order\Functional;

/**
 * Goods receipt permission tests.
 *
 * @covers \Drupal\se_goods_receipt
 * @group se_goods_receipt
 * @group stratoserp
 */
class PurchaseOrderPermissionsTest extends PurchaseOrderTestBase {

  /**
   * Faker factory for purchase order.
   *
   * @var \Faker\Factory
   */
  protected $purchaseOrder;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/purchase-order/add',
    '/se/customers/purchase-order-list',
    '/se/suppliers/purchase-order-list',
  ];

  /**
   * Test the goods receipt permissions.
   */
  public function testPurchaseOrderPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
