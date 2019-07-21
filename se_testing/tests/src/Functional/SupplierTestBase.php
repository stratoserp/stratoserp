<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\SupplierTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 *
 */
class SupplierTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use SupplierTestTrait;
  use UserCreateTrait;

  protected $supplier;

  /**
   *
   */
  protected function setUp() {
    parent::setUp();
    $this->supplierFakerSetup();
  }

}
