<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\InvoiceTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

class InvoiceTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use CustomerTestTrait;
  use UserCreateTrait;
  use InvoiceTestTrait;

  protected $customer;
  protected $invoice;

  protected function setUp() {
    parent::setUp();
    $this->invoiceFakerSetup();
  }

}
