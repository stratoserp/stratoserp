<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

/**
 * Test various Business crud operations.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessCrudTest extends BusinessTestBase {

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * Add a Business.
   */
  public function testBusinessAdd(): void {
    $customer = $this->setupCustomerUser();
    $staff = $this->setupStaffUser();
    $owner = $this->setupOwnerUser();

    $this->drupalLogin($customer);
    $this->addBusiness(FALSE);
    $this->drupalLogout();

    $this->drupalLogin($staff);
    $this->addBusiness();
    $this->drupalLogout();

    $this->drupalLogin($owner);
    $this->addBusiness();
    $this->drupalLogout();
  }

  /**
   * Edit a business.
   */
  public function testBusinessEdit(): void {
    $customer = $this->setupCustomerUser();
    $staff = $this->setupStaffUser();
    $owner = $this->setupOwnerUser();

    // Create a business for testing.
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();
    $this->drupalLogout();

    // Ensure customers can't edit a business.
    $this->drupalLogin($customer);
    $this->editEntity($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a business.
    $this->drupalLogin($staff);
    $this->editEntity($testBusiness, TRUE);
    $this->drupalLogout();

    // Ensure administrator can delete a business.
    $this->drupalLogin($owner);
    $this->editEntity($testBusiness, TRUE);
    $this->drupalLogout();
  }

  /**
   * Delete a business.
   */
  public function testBusinessDelete(): void {
    $customer = $this->setupCustomerUser();
    $staff = $this->setupStaffUser();
    $owner = $this->setupOwnerUser();

    // Create a business for testing.
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();
    $this->drupalLogout();

    // Ensure customers can't edit a business.
    $this->drupalLogin($customer);
    $this->deleteEntity($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a business.
    $this->drupalLogin($staff);
    $this->deleteEntity($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure administrator can delete a business.
    $this->drupalLogin($owner);
    $this->deleteEntity($testBusiness, TRUE);
    $this->drupalLogout();
  }

}
