<?php

declare(strict_types=1);

namespace Drupal\Tests\se_purchase_order\Functional;

use Drupal\se_goods_receipt\Controller\GoodsReceiptController;
use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Test purchase order Add/Delete.
 *
 * @covers \Drupal\se_purchase_order
 * @uses \Drupal\se_customer\Entity\Customer
 * @uses \Drupal\se_item\Entity\Item
 * @group se_purchase_order
 * @group stratoserp
 */
class PurchaseOrderCrudTest extends PurchaseOrderTestBase {

  use ItemTestTrait;
  use CustomerTestTrait;

  /**
   * Add a customer, quote, purchase order
   */
  public function testPurchaseOrderAdd() {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();

    $items = $this->createItems();
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addPurchaseOrder($testCustomer, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addPurchaseOrder($testCustomer, $items);
    $this->drupalLogout();
  }

  public function testPurchaseOrderToGoodsReceipt() {
    $this->drupalLogin($this->staff);
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $purchaseOrder = $this->addPurchaseOrder($testCustomer, $items);

    $goodsReceipt = \Drupal::classResolver(GoodsReceiptController::class)->createGoodsReceiptFromPurchaseOrder($purchaseOrder);
    $goodsReceipt->save();
    $this->markEntityForCleanup($goodsReceipt);

    $this->drupalLogout();
  }

}
