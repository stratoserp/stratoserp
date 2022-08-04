<?php

declare(strict_types=1);

namespace Drupal\Tests\se_payment\Functional;

use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Controller\PaymentController;
use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Payment create/update/delete tests.
 *
 * @coversDefault Drupal\se_payment
 * @group se_payment
 * @group stratoserp
 */
class PaymentCrudTest extends PaymentTestBase {

  use ItemTestTrait;
  use CustomerTestTrait;
  use InvoiceTestTrait;

  /**
   * Test adding a payment.
   */
  public function testPaymentAdd(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $payment = $this->addPayment([$invoice]);

    // Need to reload the invoice before checking.
    $invoice = Invoice::load($invoice->id());
    self::assertTrue($this->checkInvoicePaymentStatus($invoice, $payment));

    // Delete the payment so testing doesn't bork.
    $payment->delete();

    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $invoice = $this->addInvoice($testCustomer, $items);
    $payment = $this->addPayment([$invoice]);

    // Need to reload the invoice before checking.
    $invoice = Invoice::load($invoice->id());
    self::assertTrue($this->checkInvoicePaymentStatus($invoice, $payment));

    $this->drupalLogout();
  }

  /**
   * Test paying multiple invoices with one payment.
   */
  public function testPaymentMultipleInvoices(): void {
    $invoices = [];

    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $invoices[] = $this->addInvoice($testCustomer, $items);

    $items = $this->createItems();
    $invoices[] = $this->addInvoice($testCustomer, $items);

    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $payment = $this->addPayment($invoices);
    foreach ($invoices as $invoice) {
      $invoice = Invoice::load($invoice->id());
      self::assertTrue($this->checkInvoicePaymentStatus($invoice, $payment));
    }

    $this->drupalLogout();
  }

  /**
   * Test paying a single invoice over multiple payments.
   */
  public function testPaymentMultiplePayments(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);

    $count = 10;
    $paymentAmount = $invoice->getTotal() / $count;

    $this->drupalLogin($this->staff);
    for ($i = 0; $i < $count; $i++) {
      $invoice = Invoice::load($invoice->id());
      $this->addPaymentAmount($invoice, $paymentAmount);
    }

    self::assertEquals(0, $invoice->getOutstanding());
    self::assertEquals(0, $invoice->getInvoiceBalance());
    if ($closedTerm = \Drupal::service('se_invoice.service')->getClosedTerm()->id()) {
      self::assertEquals($invoice->se_status_ref->target_id, $closedTerm);
    }

    $this->drupalLogout();
  }

  /**
   * Test deleting a payment.
   */
  public function testPaymentDelete(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);
    $payment = $this->addPayment([$invoice]);
    $this->drupalLogout();

    // Ensure customers can't delete payments.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($payment, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete contacts.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($payment, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($payment, TRUE);
    $this->drupalLogout();
  }

  /**
   * Test creating a payment from an invoice.
   */
  public function testInvoiceToPayment() {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);

    // Now create a payment from the outstanding invoice.
    $payment = \Drupal::classResolver(PaymentController::class)->createPaymentFromInvoice($invoice);
    self::assertEquals($invoice->getTotal(), $payment->getTotal());
    $payment->save();
    $this->markEntityForCleanup($payment);

    $invoice1 = $this->addInvoice($testCustomer, $items);
    $items = $this->createItems();
    $invoice2 = $this->addInvoice($testCustomer, $items);
    $items = $this->createItems();
    $invoice3 = $this->addInvoice($testCustomer, $items);

    $total = $invoice1->getTotal() + $invoice2->getTotal() + $invoice3->getTotal();

    // Now create a payment from the outstanding invoice(s).
    $payment = \Drupal::classResolver(PaymentController::class)->createPaymentFromInvoice($invoice);
    self::assertEquals($total, $payment->getTotal());
    $payment->save();
    $this->markEntityForCleanup($payment);

    $this->drupalLogout();
  }

}
