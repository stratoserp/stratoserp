<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait InvoiceTestTrait {

  /**
   * Storage for the faker data for invoice.
   */
  protected $invoice;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function invoiceFakerSetup(): void {
    $this->faker = Factory::create();

    $original                     = error_reporting(0);
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

  /**
   * Add an invoice node.
   *
   * @param \Drupal\node\Entity\Node $test_customer
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\node\Entity\Node
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addInvoice(Node $test_customer, array $items = []): Node {
    $this->invoiceFakerSetup();

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
      'type' => 'se_invoice',
      'title' => $this->invoice->name,
      'se_bu_ref' => ['target_id' => $test_customer->id()],
      'se_in_phone' => $this->invoice->phoneNumber,
      'se_in_email' => $this->invoice->companyEmail,
      'se_in_lines' => $lines,
    ]);

    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->invoice->name, $content);

    return $node;
  }

}
