<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\QuoteTestTrait;

/**
 *
 */
class QuoteTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use CustomerTestTrait;
  use QuoteTestTrait;

  protected $customer;
  protected $quote;

  /**
   *
   */
  protected function setUp() {
    parent::setUp();
    $this->quoteFakerSetup();
  }

}
