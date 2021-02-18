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
    $this->addInvoice($testBusiness, $items);

    $this->drupalLogout();
  }

}
