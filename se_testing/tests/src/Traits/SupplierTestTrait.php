<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait SupplierTestTrait {

  public function supplierFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->supplier->name          = $this->faker->text;
    $this->supplier->phoneNumber   = $this->faker->phoneNumber;
    $this->supplier->mobileNumber  = $this->faker->phoneNumber;
    $this->supplier->streetAddress = $this->faker->streetAddress;
    $this->supplier->suburb        = $this->faker->city;
    $this->supplier->state         = $this->faker->stateAbbr;
    $this->supplier->postcode      = $this->faker->postcode;
    $this->supplier->url           = $this->faker->url;
    $this->supplier->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  public function addSupplier() {
    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_supplier',
      'title' => $this->supplier->name,
      'field_su_phone' => $this->supplier->phoneNumber,
      'field_su_email' => $this->supplier->companyEmail,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->supplier->name, $this->getTextContent());
    $this->assertContains($this->supplier->phoneNumber, $this->getTextContent());

    return $node;
  }


}