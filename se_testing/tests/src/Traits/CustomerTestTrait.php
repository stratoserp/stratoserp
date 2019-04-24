<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait CustomerTestTrait {

  public function customerFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->customer->name          = $this->faker->text;
    $this->customer->phoneNumber   = $this->faker->phoneNumber;
    $this->customer->mobileNumber  = $this->faker->phoneNumber;
    $this->customer->streetAddress = $this->faker->streetAddress;
    $this->customer->suburb        = $this->faker->city;
    $this->customer->state         = $this->faker->stateAbbr;
    $this->customer->postcode      = $this->faker->postcode;
    $this->customer->url           = $this->faker->url;
    $this->customer->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  public function addCustomer() {
    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_customer',
      'title' => $this->customer->name,
      'field_cu_phone' => $this->customer->phoneNumber,
      'field_cu_email' => $this->customer->companyEmail,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->customer->name, $this->getTextContent());
    $this->assertContains($this->customer->phoneNumber, $this->getTextContent());

    return $node;
  }


}