<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test various Customer crud operations.
 *
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerCrudTest extends FunctionalTestBase {

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * Add a Customer.
   */
  public function testCustomerAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->addCustomer();

    $this->drupalLogout();
  }

  /**
   * Delete a customer.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCustomerDelete(): void {
    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a customer for testing.
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();
    $this->drupalLogout();

    // Ensure customer can't delete customers.
    $this->drupalLogin($customer);
    $this->deleteCustomer($testCustomer, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete customers.
    $this->drupalLogin($staff);
    $this->deleteCustomer($testCustomer, TRUE);
    $this->drupalLogout();

  }

}
