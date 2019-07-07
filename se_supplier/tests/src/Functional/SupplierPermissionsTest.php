<?php

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 *
 */
class SupplierPermissionsTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_supplier',
    '/se/supplier-list'
  ];

  public function testSupplierPermissions() {

    $this->basicPermissionCheck($this->pages);

  }

}