<?php

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\CustomerTestBase;

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