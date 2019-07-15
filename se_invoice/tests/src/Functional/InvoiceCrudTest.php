<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_testing\Functional\InvoiceTestBase;
use Drupal\Tests\se_testing\Traits\StockItemTestTrait;

/**
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 *
 */
class InvoiceCrudTest extends InvoiceTestBase {

  use StockItemTestTrait;

  protected $invoice;
  protected $staff;

  public function testInvoiceAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    // Add some stock items.
    $count = random_int(5, 10);
    $items = [];
    for ($i = 0; $i < $count; $i++) {
      $this->stockItemFakerSetup();
      $items[$i] = [
        'item' => $this->addStockItem(),
        'quantity' => random_int(5, 10),
      ];
    }

    $this->invoiceFakerSetup();
    $invoice = $this->addInvoice($customer, $items);

    $this->drupalLogout();
  }

}