<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Invoice create/update/delete tests.
 *
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 */
class InvoiceCrudTest extends FunctionalTestBase {

  use ItemTestTrait;

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
  public function testInvoiceStockAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();

    $items = $this->createItems();
    $this->addInvoice($testBusiness, $items);

    $this->drupalLogout();
  }

}
