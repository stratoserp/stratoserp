<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait QuoteTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function quoteFakerSetup() {
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

  /**
   *
   */
  public function addQuote(Node $test_customer, array $items = []) {

    $lines = [];
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
      ];
      $lines[] = $line;
    }

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_quote',
      'title' => $this->quote->name,
      'field_bu_ref' => ['target_id' => $test_customer->id()],
      'field_qu_phone' => $this->quote->phoneNumber,
      'field_qu_email' => $this->quote->companyEmail,
      'field_qu_lines' => $lines,
    ]);

    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertNotContains('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertContains($this->quote->name, $content);

    return $node;
  }

}
