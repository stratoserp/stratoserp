<?php

declare(strict_types=1);

namespace Drupal\Tests\se_invoice\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait InvoiceTestTrait {

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
   * Add an Invoice entity.
   *
   * @param \Drupal\se_business\Entity\Business $testBusiness
   *   The Business to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The Invoice to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException|\Drupal\Core\Entity\EntityStorageException
   */
  public function addInvoice(Business $testBusiness, array $items = []): Invoice {

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

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $this->createInvoice([
      'name' => $this->invoice->name,
      'se_bu_ref' => ['target_id' => $testBusiness->id()],
      'se_in_phone' => $this->invoice->phoneNumber,
      'se_in_email' => $this->invoice->companyEmail,
      'se_in_lines' => $lines,
    ]);

    self::assertNotEquals($invoice, FALSE);
    self::assertNotNull($invoice->se_in_total->value);

    // Ensure that the items are present and valid.
    foreach ($invoice->se_in_lines as $line) {
      self::assertNotNull($line->price);
    }

    $this->drupalGet($invoice->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->invoice->name, $content);

    return $invoice;
  }

  /**
   * Check the payment status of an invoice.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to check.
   * @param \Drupal\se_payment\Entity\Payment $payment
   *   The payment to confirm.
   *
   * @return bool
   *   Whether the payment finalises the invoice.
   */
  public function checkInvoicePaymentStatus(Invoice $invoice, Payment $payment): bool {
    self::assertEquals($invoice->se_in_total->value, $payment->se_pa_total->value);

    return TRUE;
  }

  /**
   * Create and save a Business entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Business entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Business entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createInvoice(array $settings = []) {
    $invoice = $this->createInvoiceContent($settings);

    $invoice->save();

    return $invoice;
  }

  /**
   * Create but dont save a Business entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Business entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Business entity.
   */
  public function createInvoiceContent(array $settings = []) {
    $settings += [
      'type' => 'se_invoice',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->business->user = User::load(\Drupal::currentUser()->id());
      if ($this->business->user) {
        $settings['uid'] = $this->business->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->business->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->business->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Invoice::create($settings);
  }

}
