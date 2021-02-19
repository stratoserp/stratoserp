<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Functional;

use Drupal\Tests\se_quote\Traits\QuoteTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Quote entity.
 */
class QuoteTestBase extends FunctionalTestBase {

  use QuoteTestTrait;

  /**
   * Storage for the faker data for quote.
   *
   * @var \Faker\Factory
   */
  protected $quote;

  /**
   * Setup the basic business tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->quoteFakerSetup();
  }

}
