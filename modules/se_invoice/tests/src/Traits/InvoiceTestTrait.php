<?php

// Strict types here breaks the item line saving tests.
// declare(strict_types=1);

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

  protected $invoiceName;
  protected $invoicePhoneNumber;
  protected $invoiceMobileNumber;
  protected $invoiceStreetAddress;
  protected $invoiceSuburb;
  protected $invoiceState;
  protected $invoicePostcode;
  protected $invoiceUrl;
  protected $invoiceCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function invoiceFakerSetup(): void {
    $this->faker = Factory::create();

    $this->invoiceName          = $this->faker->text(45);
    $this->invoicePhoneNumber   = $this->faker->phoneNumber();
    $this->invoiceMobileNumber  = $this->faker->phoneNumber();
    $this->invoiceStreetAddress = $this->faker->streetAddress();
    $this->invoiceSuburb        = $this->faker->city();
    $this->invoiceState         = $this->faker->stateAbbr();
    $this->invoicePostcode      = $this->faker->postcode();
    $this->invoiceUrl           = $this->faker->url();
    $this->invoiceCompanyEmail  = $this->faker->companyEmail();
  }

  /**
   * Add an Invoice entity.
   *
   * @param \Drupal\se_business\Entity\Business $testBusiness
   *   The Business to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_invoice\Entity\Invoice|null
   *   The Invoice to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addInvoice(Business $testBusiness, array $items = [], bool $allowed = TRUE): ?Invoice {
    if (!isset($this->invoiceName)) {
      $this->invoiceFakerSetup();
    }

    $lines = [];
    $total = 0;
    foreach ($items as $item) {
      $line = [
        'target_type' => 'se_item',
        'target_id' => $item['item']->id(),
        'quantity' => $item['quantity'],
        'price' => $item['item']->se_it_sell_price->value,
      ];
      $lines[] = $line;
      $total += $item['quantity'] * $item['item']->se_it_sell_price->value;
    }

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $this->createInvoice([
      'type' => 'se_invoice',
      'name' => $this->invoiceName,
      'se_bu_ref' => $testBusiness,
      'se_in_phone' => $this->invoicePhoneNumber,
      'se_in_email' => $this->invoiceCompanyEmail,
      'se_in_lines' => $lines,
    ]);
    self::assertNotEquals($invoice, FALSE);
    self::assertNotNull($invoice->se_in_total->value);
    self::assertEquals($total, $invoice->se_in_total->value);

    // Ensure that the items are present and valid.
    foreach ($invoice->se_in_lines as $line) {
      self::assertNotNull($line->price);
    }

    $this->drupalGet($invoice->toUrl());

    $content = $this->getTextContent();

    if (!$allowed) {
      // Equivalent to 403 status.
      self::assertStringContainsString('Access denied', $content);
      return NULL;
    }

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->invoiceName, $content);

    return $invoice;
  }

  /**
   * Adjust an existing invoice.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The Invoice we're testing with.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The Adjusted invoice.
   */
  public function adjustInvoiceIncrease(Invoice $invoice): Invoice {
    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $invoice->se_bu_ref->entity;
    $businessOldBalance = \Drupal::service('se_business.service')->getBalance($business);

    $oldTotal = $invoice->se_in_total->value;
    $oldOutstanding = $invoice->se_in_outstanding->value;

    self::assertEquals($invoice->se_in_total->value, $businessOldBalance);

    $newTotal = 0;
    foreach ($invoice->se_in_lines as $line) {
      $line->price += rand(10, 20);
      $newTotal += $line->quantity * $line->price;
    }

    $invoice->save();
    $this->markEntityForCleanup($invoice);

    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $invoice->se_bu_ref->entity;

    self::assertNotEquals($invoice->se_in_total->value, $oldTotal);
    self::assertNotEquals($invoice->se_in_outstanding->value, $oldOutstanding);
    self::assertEquals($invoice->se_in_total->value, $newTotal);
    self::assertEquals($invoice->se_in_outstanding->value, $newTotal);

    $businessNewBalance = \Drupal::service('se_business.service')->getBalance($business);
    self::assertEquals($invoice->se_in_total->value, $businessNewBalance);

    return $invoice;
  }

  /**
   * Adjust an existing invoice.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The Invoice we're testing with.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The Adjusted invoice.
   */
  public function adjustInvoiceDecrease(Invoice $invoice): Invoice {
    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $invoice->se_bu_ref->entity;
    $businessOldBalance = \Drupal::service('se_business.service')->getBalance($business);

    $oldTotal = $invoice->se_in_total->value;
    $oldOutstanding = $invoice->se_in_outstanding->value;

    self::assertEquals($invoice->se_in_total->value, $businessOldBalance);

    $newTotal = 0;
    foreach ($invoice->se_in_lines as $line) {
      $line->price -= rand(10, 20);
      $newTotal += $line->quantity * $line->price;
    }

    $invoice->save();
    $this->markEntityForCleanup($invoice);

    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $invoice->se_bu_ref->entity;

    self::assertNotEquals($invoice->se_in_total->value, $oldTotal);
    self::assertNotEquals($invoice->se_in_outstanding->value, $oldOutstanding);
    self::assertEquals($invoice->se_in_total->value, $newTotal);
    self::assertEquals($invoice->se_in_outstanding->value, $newTotal);

    $businessNewBalance = \Drupal::service('se_business.service')->getBalance($business);
    self::assertEquals($invoice->se_in_total->value, $businessNewBalance);

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
   * Create and save an Invoice entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Invoice entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Invoice entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createInvoice(array $settings = []) {
    $invoice = $this->createInvoiceContent($settings);

    $invoice->save();
    $this->markEntityForCleanup($invoice);

    return $invoice;
  }

  /**
   * Create but dont save a Invoice entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Invoice entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Invoice entity.
   */
  public function createInvoiceContent(array $settings = []) {
    $settings += [
      'type' => 'se_invoice',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->invoiceUser = User::load(\Drupal::currentUser()->id());
      if ($this->invoiceUser) {
        $settings['uid'] = $this->invoiceUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->invoiceUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->invoiceUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Invoice::create($settings);
  }

}
