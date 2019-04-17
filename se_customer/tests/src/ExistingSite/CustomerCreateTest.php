<?php

namespace Drupal\Tests\se_customer\ExistingSite;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerCreateTest extends CustomerTestBase {

  protected $staff;

  public function testCustomerAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();

    $this->drupalLogout();

  }

}