<?php

namespace Drupal\Tests\se_invoice\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 *
 * Strict types in this file breaks the item line saving tests.
 * declare(strict_types=1);
 */
trait InvoiceTestTrait {

  protected string $invoiceName;
  protected string $invoicePhoneNumber;
  protected string $invoiceMobileNumber;
  protected string $invoiceStreetAddress;
  protected string $invoiceSuburb;
  protected string $invoiceState;
  protected string $invoicePostcode;
  protected string $invoiceUrl;
  protected string $invoiceCompanyEmail;

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
   * @param \Drupal\se_customer\Entity\Customer $testCustomer
   *   The Customer to associate the Invoice with.
   * @param array $items
   *   An array of items to use for invoice lines.
   *
   * @return \Drupal\se_invoice\Entity\Invoice|null
   *   The Invoice to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addInvoice(Customer $testCustomer, array $items = []): ?Invoice {
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
        'price' => $item['item']->se_sell_price->value,
        'cost' => $item['item']->se_cost_price->value,
      ];
      $lines[] = $line;
      $total += $line['quantity'] * $line['price'];
    }

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $this->createInvoice([
      'type' => 'se_invoice',
      'name' => $this->invoiceName,
      'se_cu_ref' => $testCustomer,
      'se_phone' => $this->invoicePhoneNumber,
      'se_email' => $this->invoiceCompanyEmail,
      'se_item_lines' => $lines,
      'se_total' => $total,
      'se_outstanding' => $total,
    ]);
    self::assertNotEquals($invoice, FALSE);
    self::assertNotNull($invoice->getTotal());
    self::assertEquals($total, $invoice->getTotal());
    self::assertNotNull($invoice->getOutstanding());
    self::assertEquals($invoice->getOutstanding(), $invoice->getTotal());

    // Ensure that the items are present and valid.
    foreach ($invoice->se_item_lines as $line) {
      self::assertNotNull($line->price);
      self::assertNotNull($line->cost);
    }

    $this->drupalGet($invoice->toUrl());

    $content = $this->getTextContent();

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
    /** @var \Drupal\se_customer\Entity\Customer $customer */
    $customer = $invoice->getCustomer();
    $customerOldBalance = $customer->getBalance();

    $oldTotal = $invoice->getTotal();
    $oldOutstanding = $invoice->getOutstanding();

    self::assertEquals($invoice->getTotal(), $customerOldBalance);

    $newTotal = 0;
    foreach ($invoice->se_item_lines as $line) {
      $line->price += rand(10, 20);
      $newTotal += $line->quantity * $line->price;
    }

    $invoice->save();

    $invoice = Invoice::load($invoice->id());

    self::assertNotEquals($invoice->getTotal(), $oldTotal);
    self::assertNotEquals($invoice->getOutstanding(), $oldOutstanding);
    self::assertEquals($invoice->getTotal(), $newTotal);
    self::assertEquals($invoice->getOutstanding(), $newTotal);

    $customerNewBalance = $customer->getBalance();
    self::assertEquals($invoice->getTotal(), $customerNewBalance);

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
    /** @var \Drupal\se_customer\Entity\Customer $customer */
    $customer = $invoice->getCustomer();
    $customerOldBalance = $customer->getBalance();

    $oldTotal = $invoice->getTotal();
    $oldOutstanding = $invoice->getOutstanding();

    self::assertEquals($invoice->getTotal(), $customerOldBalance);

    $newTotal = 0;
    foreach ($invoice->se_item_lines as $line) {
      $line->price -= rand(10, 20);
      $newTotal += $line->quantity * $line->price;
    }

    $invoice->save();

    $invoice = Invoice::load($invoice->id());

    self::assertNotEquals($oldTotal, $invoice->getTotal());
    self::assertNotEquals($oldOutstanding, $invoice->getOutstanding());
    self::assertEquals($newTotal, $invoice->getTotal());
    self::assertEquals($newTotal, $invoice->getOutstanding());

    $customerNewBalance = $customer->getBalance();
    self::assertEquals($customerNewBalance, $invoice->getTotal());

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
    if (count($payment->se_payment_lines) > 1) {
      foreach ($payment->se_payment_lines as $paymentLine) {
        if ($paymentLine->target_id == $invoice->id()) {
          self::assertEquals($paymentLine->amount, $invoice->getTotal());
        }
      }
    }
    else {
      self::assertEquals($payment->getTotal(), $invoice->getTotal());
    }
    self::assertEquals(0, $invoice->getOutstanding());
    self::assertEquals(0, $invoice->getInvoiceBalance());
    if ($closedTerm = \Drupal::service('se_invoice.service')->getClosedTerm()->id()) {
      self::assertEquals($closedTerm, $invoice->se_status_ref->target_id);
    }
    return TRUE;
  }

  /**
   * Create and save an Invoice entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Invoice entity.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The created Invoice entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createInvoice(array $settings = []): Invoice {
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
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The created but not yet saved Invoice entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createInvoiceContent(array $settings = []): Invoice {
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
