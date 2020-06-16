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
   * @var FakerFactory
   */
  protected $supplier;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function supplierFakerSetup(): void {
    $this->faker = Factory::create();

    $original                      = error_reporting(0);
    $this->supplier->name          = $this->faker->realText(50);
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

  /**
   * Add a supplier node.
   */
  public function addSupplier(): Node {
    $this->supplierFakerSetup();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_supplier',
      'title' => $this->supplier->name,
      'se_su_phone' => $this->supplier->phoneNumber,
      'se_su_email' => $this->supplier->companyEmail,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->supplier->name, $content);
    $this->assertStringContainsString($this->supplier->phoneNumber, $content);

    return $node;
  }

}
