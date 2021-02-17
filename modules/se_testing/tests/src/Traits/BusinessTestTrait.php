<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait BusinessTestTrait {

  /**
   * Storage for the faker data for business.
   *
   * @var \Faker\Factory
   */
  protected $supplier;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function businessFakerSetup(): void {
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
   * Add a business node.
   */
  public function addBusiness(): Node {
    $this->businessFakerSetup();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_business',
      'title' => $this->supplier->name,
      'se_cu_phone' => $this->supplier->phoneNumber,
      'se_cu_email' => $this->supplier->companyEmail,
    ]);
    $this->assertNotEquals($node, FALSE);
    $this->drupalGet($node->toUrl());

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->supplier->name, $content);
    $this->assertStringContainsString($this->supplier->phoneNumber, $content);

    return $node;
  }

}
