<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\StockItemTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

class StockItemTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use StockItemTestTrait;
  use UserCreateTrait;

  protected $stockItem;

  protected function setUp() {
    parent::setUp();
    $this->stockItemFakerSetup();
  }

}
