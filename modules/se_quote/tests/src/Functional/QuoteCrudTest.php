<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Functional;

use Drupal\se_invoice\Controller\InvoiceController;
use Drupal\se_purchase_order\Controller\PurchaseOrderController;
use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Quote create/update/delete tests.
 *
 * @covers \Drupal\se_quote\Entity\Quote
 * @uses \Drupal\se_customer\Entity\Customer
 * @uses \Drupal\se_invoice\Entity\Invoice
 * @uses \Drupal\se_item\Entity\Item
 * @uses \Drupal\se_purchase_order\Entity\PurchaseOrder
 * @group se_quote
 * @group stratoserp
 */
class QuoteCrudTest extends QuoteTestBase {

  use ItemTestTrait;
  use CustomerTestTrait;

  /**
   * Test adding a quote.
   */
  public function testQuoteAdd() {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addQuote($testCustomer, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addQuote($testCustomer, $items);
    $this->drupalLogout();
  }

  /**
   * Test adding a quote and a subsequent invoice.
   */
  public function testQuoteToInvoice() {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $quote = $this->addQuote($testCustomer, $items);

    // Now create an invoice from the Timekeeping entries.
    $invoice = \Drupal::classResolver(InvoiceController::class)->createInvoiceFromQuote($quote);
    $invoice->save();
    $this->markEntityForCleanup($invoice);

    $this->drupalLogout();
  }

  /**
   * Test adding a quote and a subsequent invoice.
   */
  public function testQuoteToPurchaseOrder() {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $quote = $this->addQuote($testCustomer, $items);

    // Now create an invoice from the Timekeeping entries.
    $purchaseOrder = \Drupal::classResolver(PurchaseOrderController::class)->createPurchaseOrderFromQuote($quote);
    $purchaseOrder->save();
    $this->markEntityForCleanup($purchaseOrder);

    $this->drupalLogout();
  }

}
