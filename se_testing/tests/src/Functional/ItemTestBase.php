<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\ItemTestTrait;

class ItemTestBase extends FunctionalTestBase {

  use ItemTestTrait;

  protected $item;

  protected function setUp() {
    parent::setUp();
    $this->itemFakerSetup();
  }

}
