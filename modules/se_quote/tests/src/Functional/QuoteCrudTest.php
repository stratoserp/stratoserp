<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Functional;

use Drupal\se_invoice\Controller\InvoiceController;
use Drupal\se_purchase_order\Controller\PurchaseOrderController;
use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Quote create/update/delete tests.
 *
 * @coversDefault Drupal\se_quote
 * @group se_quote
 * @group stratoserp
 */
class QuoteCrudTest extends QuoteTestBase {

  use ItemTestTrait;
  use BusinessTestTrait;

  /**
   * Test adding a quote.
   */
  public function testQuoteAdd() {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $this->drupalLogout();

    $this->drupalLogin($this->customer);
    $this->addQuote($testBusiness, $items, FALSE);
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addQuote($testBusiness, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addQuote($testBusiness, $items);
    $this->drupalLogout();
  }

  /**
   * Test adding a quote and a subsequent invoice.
   */
  public function testQuoteToInvoice() {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $quote = $this->addQuote($testBusiness, $items);

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
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $quote = $this->addQuote($testBusiness, $items);

    // Now create an invoice from the Timekeeping entries.
    $invoice = \Drupal::classResolver(PurchaseOrderController::class)->createPurchaseOrderFromQuote($quote);
    $invoice->save();
    $this->markEntityForCleanup($invoice);

    $this->drupalLogout();
  }

}
