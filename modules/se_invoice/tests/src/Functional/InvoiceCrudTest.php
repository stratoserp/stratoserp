<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Invoice create/update/delete tests.
 *
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoiceCrudTest extends InvoiceTestBase {

  use ItemTestTrait;
  use BusinessTestTrait;

  /**
   * Test adding an invoice.
   */
  public function testInvoiceStockAdd(): void {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $this->drupalLogout();

    $this->drupalLogin($this->customer);
    $this->addInvoice($testBusiness, $items, FALSE);
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addInvoice($testBusiness, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addInvoice($testBusiness, $items);
    $this->drupalLogout();
  }

  /**
   * Test deleting an invoice.
   */
  public function testInvoiceDelete(): void {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $invoice = $this->addInvoice($testBusiness, $items);
    $this->drupalLogout();

    // Ensure customers can't delete invoices.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($invoice, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete contacts.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($invoice, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($invoice, TRUE);
    $this->drupalLogout();
  }

}
