<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Faker\Factory;

/**
 * Perform basic business tests.
 */
class BusinessTestBase extends FunctionalTestBase {

  use BusinessTestTrait;

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * Setup the basic business tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->businessFakerSetup();
  }

  /**
   * Setup basic faker fields for this test trait.
   */
  public function businessFakerSetup(): void {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->business->name = $this->faker->realText(50);
    $this->business->phoneNumber = $this->faker->phoneNumber;
    $this->business->mobileNumber = $this->faker->phoneNumber;
    $this->business->streetAddress = $this->faker->streetAddress;
    $this->business->suburb = $this->faker->city;
    $this->business->state = $this->faker->stateAbbr;
    $this->business->postcode = $this->faker->postcode;
    $this->business->url = $this->faker->url;
    $this->business->companyEmail = $this->faker->companyEmail;
    error_reporting($original);
  }

}
