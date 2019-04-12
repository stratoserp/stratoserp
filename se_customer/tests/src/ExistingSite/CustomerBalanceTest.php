<?php

namespace Drupal\Tests\se_customer\ExistingSite;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerBalanceTest extends CustomerTestBase {

  public function testCustomerBalance() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $customer = $this->addCustomer();

    \Drupal::service('se_customer.service')->setBalance($customer, 25);
    $this->assertEqual(\Drupal::service('se_customer.service')->getBalance($customer), 25);

    \Drupal::service('se_customer.service')->adjustBalance($customer, -50);
    $this->assertEqual(\Drupal::service('se_customer.service')->getBalance($customer), -25);

    $this->drupalLogout();
  }

}