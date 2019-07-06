<?php

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\CustomerTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerCrudTest extends CustomerTestBase {

  protected $staff;

  public function testCustomerAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();

    $this->drupalLogout();

  }

  public function testCustomerDelete() {

    $this->customerFakerSetup();

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a contact for testing.
    $this->drupalLogin($staff);
    $contact = $this->addCustomer();
    $this->drupalLogout();

    // Ensure customer can't delete customers.
    $this->drupalLogin($customer);
    $this->deleteCustomer($contact, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete customers.
    $this->drupalLogin($staff);
    $this->deleteCustomer($contact, TRUE);
    $this->drupalLogout();

  }

}