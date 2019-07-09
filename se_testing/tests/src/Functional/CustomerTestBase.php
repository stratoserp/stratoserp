<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

class CustomerTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use CustomerTestTrait;
  use UserCreateTrait;

  protected $customer;

  protected function setUp() {
    parent::setUp();
    $this->customerFakerSetup();
  }

}
