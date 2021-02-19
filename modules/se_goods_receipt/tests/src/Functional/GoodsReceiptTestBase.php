<?php

declare(strict_types=1);

namespace Drupal\Tests\se_goods_receipt\Functional;

use Drupal\Tests\se_goods_receipt\Traits\GoodsReceiptTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic contact tests.
 */
class GoodsReceiptTestBase extends FunctionalTestBase {

  use GoodsReceiptTestTrait;

  /**
   * Storage for the faker data for goods receipt.
   *
   * @var \Faker\Factory
   */
  protected $goodsReceipt;

  /**
   * Setup the basic variables.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->goodsReceiptFakerSetup();
  }

}
