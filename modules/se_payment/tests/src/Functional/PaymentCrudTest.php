<?php

declare(strict_types=1);

namespace Drupal\Tests\se_payment\Functional;

use Drupal\se_invoice\Controller\InvoiceController;
use Drupal\se_payment\Controller\PaymentController;
use Drupal\Tests\se_business\Traits\BusinessTestTrait;
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
  use BusinessTestTrait;
  use InvoiceTestTrait;

  /**
   * Test adding an payment.
   */
  public function testPaymentAdd(): void {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testBusiness, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->customer);
    $payment = $this->addPayment($invoice, FALSE);
    if ($payment) {
      self::assertFalse($this->checkInvoicePaymentStatus($invoice, $payment));
    }
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $payment = $this->addPayment($invoice);
    self::assertTrue($this->checkInvoicePaymentStatus($invoice, $payment));
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $invoice = $this->addInvoice($testBusiness, $items);
    $payment = $this->addPayment($invoice);
    self::assertTrue($this->checkInvoicePaymentStatus($invoice, $payment));
    $this->drupalLogout();
  }

  /**
   * Test deleting an payment.
   */
  public function testPaymentDelete(): void {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testBusiness, $items);
    $payment = $this->addPayment($invoice);
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

  public function testInvoiceToPayment() {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testBusiness, $items);

    // Now create a payment from the outstanding invoice.
    $payment = \Drupal::classResolver(PaymentController::class)->createPaymentFromInvoice($invoice);
    self::assertEquals($invoice->getTotal(), $payment->getTotal());
    $payment->save();
    $this->markEntityForCleanup($payment);

    $invoice1 = $this->addInvoice($testBusiness, $items);
    $items = $this->createItems();
    $invoice2 = $this->addInvoice($testBusiness, $items);
    $items = $this->createItems();
    $invoice3 = $this->addInvoice($testBusiness, $items);

    $total = $invoice1->getTotal() + $invoice2->getTotal() + $invoice3->getTotal();

    // Now create a payment from the outstanding invoice(s).
    $payment = \Drupal::classResolver(PaymentController::class)->createPaymentFromInvoice($invoice);
    self::assertEquals($total, $payment->getTotal());
    $payment->save();
    $this->markEntityForCleanup($payment);

    $this->drupalLogout();
  }

}
