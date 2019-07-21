<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_testing\Functional\InvoiceTestBase;

/**
 * @coversDefault Drupal\se_invoice
 * @group se_invoice
 * @group stratoserp
 *
 */
class InvoicePermissionsTest extends InvoiceTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_invoice',
    '/se/customers/invoice-list',
  ];

  public function testInvoicePermissions() {

    $this->basicPermissionCheck($this->pages);
  }


}