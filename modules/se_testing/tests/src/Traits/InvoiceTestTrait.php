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
   *
   * @var FakerFactory
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
   * @param \Drupal\node\Entity\Node $testCustomer
   *   The customer to associate the node with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\node\Entity\Node
   *   The node to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function addInvoice(Node $testCustomer, array $items = []): Node {

    $this->invoiceFakerSetup();

    $lines = [];
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
        'price' => $item['item']->se_it_cost_price->value,
      ];
      $lines[] = $line;
    }

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_invoice',
      'title' => $this->invoice->name,
      'se_bu_ref' => ['target_id' => $testCustomer->id()],
      'se_in_phone' => $this->invoice->phoneNumber,
      'se_in_email' => $this->invoice->companyEmail,
      'se_in_lines' => $lines,
    ]);

    $this->assertNotEquals($node, FALSE);
    $this->assertNotNull($node->se_in_total->value);

    // Ensure that the items are present and valid.
    foreach ($node->se_in_lines as $line) {
      $this->assertNotNull($line->price);
    }

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->invoice->name, $content);

    return $node;
  }

  /**
   * Check the payment status of an invoice.
   *
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to check.
   * @param \Drupal\node\Entity\Node $payment
   *   The payment to confirm.
   *
   * @return bool
   *   Whether the payment finalises the invoice.
   */
  public function checkInvoicePaymentStatus(Node $invoice, Node $payment): bool {
    $this->assertEquals($invoice->se_in_total->value, $payment->se_pa_total->value);

    return TRUE;
  }

}
