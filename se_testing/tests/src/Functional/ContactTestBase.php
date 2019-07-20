<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\ContactTestTrait;
use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

class ContactTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use ContactTestTrait;
  use CustomerTestTrait;
  use UserCreateTrait;

  protected $contact;
  protected $customer;

  protected function setUp() {
    parent::setUp();
    $this->customerFakerSetup();
    $this->contactFakerSetup();
  }

}