<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Functional;

use Drupal\Tests\se_quote\Traits\QuoteTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Faker\Factory;

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

  /**
   * Setup basic faker fields for this test trait.
   */
  public function quoteFakerSetup(): void {
    $this->faker = Factory::create();

    $original                   = error_reporting(0);
    $this->quote->name          = $this->faker->text;
    $this->quote->phoneNumber   = $this->faker->phoneNumber;
    $this->quote->mobileNumber  = $this->faker->phoneNumber;
    $this->quote->streetAddress = $this->faker->streetAddress;
    $this->quote->suburb        = $this->faker->city;
    $this->quote->state         = $this->faker->stateAbbr;
    $this->quote->postcode      = $this->faker->postcode;
    $this->quote->url           = $this->faker->url;
    $this->quote->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

}
