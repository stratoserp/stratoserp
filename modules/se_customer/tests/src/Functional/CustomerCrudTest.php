<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

/**
 * Test various Customer crud operations.
 *
 * @covers \Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerCrudTest extends CustomerTestBase {

  /**
   * Add a Customer.
   */
  public function testCustomerAdd(): void {
    $this->drupalLogin($this->staff);
    $this->customerFakerSetup();
    $this->addCustomer();
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->customerFakerSetup();
    $this->addCustomer();
    $this->drupalLogout();
  }

  /**
   * Edit a customer.
   */
  public function testCustomerEdit(): void {
    // Create a customer for testing.
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $this->drupalLogout();

    // Ensure customers can't edit a customer.
    $this->drupalLogin($this->customer);
    $this->editEntity($testCustomer, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a customer.
    $this->drupalLogin($this->staff);
    $this->editEntity($testCustomer);
    $this->drupalLogout();

    // Ensure administrator can delete a customer.
    $this->drupalLogin($this->owner);
    $this->editEntity($testCustomer);
    $this->drupalLogout();
  }

  /**
   * Delete a customer.
   */
  public function testCustomerDelete(): void {
    // Create a customer for testing.
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $this->drupalLogout();

    // Ensure customers can't edit a customer.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testCustomer, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a customer.
    $this->drupalLogin($this->staff);
    $this->deleteEntity($testCustomer, FALSE);
    $this->drupalLogout();

    // Ensure administrator can delete a customer.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testCustomer);
    $this->drupalLogout();
  }

}
