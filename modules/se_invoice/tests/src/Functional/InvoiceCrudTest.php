<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoiceCrudTest extends FunctionalTestBase {

  use ItemTestTrait;
  use UserCreateTrait;

  protected $invoice;
  protected $staff;

  /**
   *
   */
  public function testInvoiceAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();

    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);

    $this->drupalLogout();
  }

  public function addInvoiceWithItems() {

  }

}
