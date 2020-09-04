<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * Invoice create/update/delete tests.
 *
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoiceCrudTest extends FunctionalTestBase {

  use ItemTestTrait;
  use UserCreateTrait;

  /**
   * Invoice holding var.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $invoice;

  /**
   * Faker Factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * Test adding an invoice.
   */
  public function testInvoiceAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();

    $items = $this->createItems();
    $invoice = $this->addInvoice($testCustomer, $items);

    $this->drupalLogout();
  }

  /**
   * Test adding an invoice with items.
   */
  public function addInvoiceWithItems() {

  }

}
