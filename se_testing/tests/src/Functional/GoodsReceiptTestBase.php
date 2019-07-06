<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\GoodsReceiptTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

class GoodsReceiptTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use CustomerTestTrait;
  use UserCreateTrait;
  use GoodsReceiptTestTrait;

  protected $goodsReceipt;

  protected function setUp() {
    parent::setUp();
    $this->goodsReceiptFakerSetup();
  }

}
