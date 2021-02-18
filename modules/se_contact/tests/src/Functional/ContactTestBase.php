<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_contact\Traits\ContactTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Faker\Factory;

/**
 * Perform basic contact tests.
 */
class ContactTestBase extends FunctionalTestBase {

  use ContactTestTrait;

  /**
   * Faker factory for contact.
   *
   * @var \Faker\Factory
   */
  protected $contact;

  /**
   * Setup the basic contact tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->contactFakerSetup();
  }

  /**
   * Setup basic faker fields for this test trait.
   */
  public function contactFakerSetup(): void {
    $this->faker = Factory::create();

    $original                     = error_reporting(0);
    $this->contact->name          = $this->faker->realText(50);
    $this->contact->phoneNumber   = $this->faker->phoneNumber;
    $this->contact->mobileNumber  = $this->faker->phoneNumber;
    $this->contact->streetAddress = $this->faker->streetAddress;
    $this->contact->suburb        = $this->faker->city;
    $this->contact->state         = $this->faker->stateAbbr;
    $this->contact->postcode      = $this->faker->postcode;
    $this->contact->url           = $this->faker->url;
    $this->contact->email         = $this->faker->email;
    $this->contact->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

}
