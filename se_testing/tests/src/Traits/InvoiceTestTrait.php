<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait InvoiceTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function invoiceFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->invoice->name          = $this->faker->text;
    $this->invoice->phoneNumber   = $this->faker->phoneNumber;
    $this->invoice->mobileNumber  = $this->faker->phoneNumber;
    $this->invoice->streetAddress = $this->faker->streetAddress;
    $this->invoice->suburb        = $this->faker->city;
    $this->invoice->state         = $this->faker->stateAbbr;
    $this->invoice->postcode      = $this->faker->postcode;
    $this->invoice->url           = $this->faker->url;
    $this->invoice->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  public function addInvoice(Node $test_customer, array $items = []) {

    $lines = [];
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
      ];
      $lines[] = $line;
    }

    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_invoice',
      'title' => $this->invoice->name,
      'field_bu_ref' => ['target_id' => $test_customer->id()],
      'field_in_phone' => $this->invoice->phoneNumber,
      'field_in_email' => $this->invoice->companyEmail,
      'field_in_lines' => $lines,
    ]);

    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertNotContains('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertContains($this->invoice->name, $content);

    return $node;
  }


}