<?php

declare(strict_types=1);

namespace Drupal\Tests\se_goods_receipt\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_goods_receipt
 * @group se_goods_receipt
 * @group stratoserp
 */
class GoodsReceiptPermissionsTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_goods_receipt',
    '/se/goods-receipt-list',
  ];

  /**
   *
   */
  public function testGoodsReceiptPermissions(): void {

    $this->basicPermissionCheck($this->pages);

  }

}
