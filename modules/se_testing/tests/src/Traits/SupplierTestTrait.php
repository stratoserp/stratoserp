<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait SupplierTestTrait {

  /**
   * Storage for the faker data for supplier.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function supplierFakerSetup(): void {
    $this->faker = Factory::create();

    $original                      = error_reporting(0);
    $this->business->name          = $this->faker->realText(50);
    $this->business->phoneNumber   = $this->faker->phoneNumber;
    $this->business->mobileNumber  = $this->faker->phoneNumber;
    $this->business->streetAddress = $this->faker->streetAddress;
    $this->business->suburb        = $this->faker->city;
    $this->business->state         = $this->faker->stateAbbr;
    $this->business->postcode      = $this->faker->postcode;
    $this->business->url           = $this->faker->url;
    $this->business->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a supplier node.
   */
  public function addSupplier(): Node {
    $this->supplierFakerSetup();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_supplier',
      'title' => $this->business->name,
      'se_su_phone' => $this->business->phoneNumber,
      'se_su_email' => $this->business->companyEmail,
    ]);
    $this->assertNotEquals($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->business->name, $content);
    $this->assertStringContainsString($this->business->phoneNumber, $content);

    return $node;
  }

}
