<?php

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_testing\Functional\SupplierTestBase;

/**
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierCreateTest extends SupplierTestBase {

  protected $staff;

  /**
   *
   */
  public function testSupplierCreate() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $supplier = $this->addSupplier();

    $this->drupalLogout();

  }

}
