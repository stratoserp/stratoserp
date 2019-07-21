<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ContactTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function contactFakerSetup() {
    $this->faker = Factory::create();

    $original                     = error_reporting(0);
    $this->contact->name          = $this->faker->text;
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

  /**
   *
   */
  public function addContact(Node $test_customer) {

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_contact',
      'title' => $this->contact->name,
      'field_co_phone' => $this->contact->phoneNumber,
      'field_bu_ref' => ['target_id' => $test_customer->id()],
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());

    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();
    $this->assertNotContains('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertContains($this->contact->name, $content);
    $this->assertContains($this->contact->phoneNumber, $content);
    $this->assertContains($this->customer->name, $content);

    return $node;
  }

}
