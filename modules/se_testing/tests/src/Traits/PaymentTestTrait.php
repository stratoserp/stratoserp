<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait PaymentTestTrait {

  /**
   * Storage for the faker data for payment.
   *
   * @var FakerFactory
   */
  protected $payment;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function paymentFakerSetup(): void {
    $this->faker = Factory::create();

    $original            = error_reporting(0);
    $this->payment->name = $this->faker->text;
    error_reporting($original);
  }

  /**
   * Add a payment and set the customer to the value passed in.
   *
   * @param \Drupal\node\Entity\Node $invoice
   *   The invoice to associate the payment with.
   *
   * @return \Drupal\node\Entity\Node
   *   The node to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function addPayment(Node $invoice = NULL): Node {

    $this->paymentFakerSetup();

    $lines = [];
    $line = [
      'target_type' => 'se_invoice',
      'target_id' => $invoice->id(),
      'amount' => $invoice->se_in_total->value,
    ];
    $lines[] = $line;

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_payment',
      'title' => $this->payment->name,
      'se_pa_lines' => $lines,
    ]);

    $this->assertNotEquals($node, FALSE);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->payment->name, $content);

    return $node;
  }

}
