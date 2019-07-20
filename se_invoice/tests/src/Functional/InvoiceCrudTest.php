<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\InvoiceTestBase;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 *
 */
class InvoiceCrudTest extends InvoiceTestBase {

  use ItemTestTrait;
  use UserCreateTrait;

  protected $invoice;
  protected $staff;

  public function testInvoiceAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $this->customerFakerSetup();
    $test_customer = $this->addCustomer();

    // Add some stock items.
    $count = random_int(5, 10);
    $items = [];
    for ($i = 0; $i < $count; $i++) {
      $this->itemFakerSetup();
      $items[$i] = [
        'item' => $this->addItem('se_stock'),
        'quantity' => random_int(5, 10),
      ];
    }

    $this->invoiceFakerSetup();
    $invoice = $this->addInvoice($test_customer, $items);

    $this->drupalLogout();
  }

}