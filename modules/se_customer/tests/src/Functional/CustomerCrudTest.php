<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerCrudTest extends FunctionalTestBase {

  protected $staff;

  /**
   *
   */
  public function testCustomerAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->addCustomer();

    $this->drupalLogout();
  }

  /**
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testCustomerDelete(): void {
    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a contact for testing.
    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $this->drupalLogout();

    // Ensure customer can't delete customers.
    $this->drupalLogin($customer);
    $this->deleteNode($test_customer, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete customers.
    $this->drupalLogin($staff);
    $this->deleteNode($test_customer, TRUE);
    $this->drupalLogout();

  }

}
