<?php

namespace Drupal\Tests\se_supplier\ExistingSite;

/**
 * @coversDefault Drupal\se_supplier
 * @group se_customer
 * @group stratoserp
 *
 */
class SupplierCreateTest extends SupplierTestBase {

  protected $staff;

  public function testSupplierAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $supplier = $this->addSupplier();

    $this->drupalLogout();

  }

}