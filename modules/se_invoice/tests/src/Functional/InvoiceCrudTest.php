<?php

declare(strict_types=1);

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Invoice create/update/delete tests.
 *
 * @covers \Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoiceCrudTest extends InvoiceTestBase {

  use ItemTestTrait;
  use CustomerTestTrait;

  /**
   * Test adding an invoice.
   */
  public function testInvoiceStockAdd(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();

    // Ensure the 'Add invoice' link is visible.
    $this->drupalGet($testCustomer->toUrl());
    $session = $this->assertSession();
    $session->pageTextContains('Add invoice');

    $items = $this->createItems();
    $this->drupalLogout();

    // Ensure staff can add invoices.
    $this->drupalLogin($this->staff);
    $this->addInvoice($testCustomer, $items);
    $this->drupalLogout();

    // Ensure owners can add invoices.
    $this->drupalLogin($this->owner);
    $this->addInvoice($testCustomer, $items);
    $this->drupalLogout();
  }

  /**
   * Test adding and adjusting an invoice.
   */
  public function testInvoiceAdjustment(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $this->drupalLogout();

    // Add an invoice as staff.
    $this->drupalLogin($this->staff);
    $invoice = $this->addInvoice($testCustomer, $items);
    $this->adjustInvoiceIncrease($invoice);
    $this->adjustInvoiceDecrease($invoice);

    $this->drupalLogout();
  }

  /**
   * Test deleting an invoice.
   */
  public function testInvoiceDelete(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);
    $this->drupalLogout();

    // Ensure customers can't delete invoices.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($invoice, FALSE);
    $this->drupalLogout();

    // Ensure customer can't delete invoices.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($invoice, FALSE);
    $this->drupalLogout();

    // Ensure owners can delete invoices.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($invoice, TRUE);
    $this->drupalLogout();
  }

}
