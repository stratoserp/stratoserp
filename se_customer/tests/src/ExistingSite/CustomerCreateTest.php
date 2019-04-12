<?php

namespace Drupal\Tests\se_customer\ExistingSite;

use Drupal\se_testing\Traits\CustomerCreateTrait;


/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerCreateTest extends CustomerTestBase {

  use CustomerCreateTrait;

  protected $staff;

  public function testCustomerAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $customer = $this->addCustomer();

    $this->drupalLogout();

  }

}