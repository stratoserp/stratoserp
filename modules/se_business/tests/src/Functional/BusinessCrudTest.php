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
   * Add a Business.
   */
  public function testBusinessAdd(): void {
    $this->drupalLogin($this->customer);
    $this->addBusiness('Customer', FALSE);
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->businessFakerSetup();
    $this->addBusiness();
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->businessFakerSetup();
    $this->addBusiness();
    $this->drupalLogout();
  }

  /**
   * Edit a business.
   */
  public function testBusinessEdit(): void {
    // Create a business for testing.
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $this->drupalLogout();

    // Ensure customers can't edit a business.
    $this->drupalLogin($this->customer);
    $this->editEntity($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a business.
    $this->drupalLogin($this->staff);
    $this->editEntity($testBusiness);
    $this->drupalLogout();

    // Ensure administrator can delete a business.
    $this->drupalLogin($this->owner);
    $this->editEntity($testBusiness);
    $this->drupalLogout();
  }

  /**
   * Delete a business.
   */
  public function testBusinessDelete(): void {
    // Create a business for testing.
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $this->drupalLogout();

    // Ensure customers can't edit a business.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a business.
    $this->drupalLogin($this->staff);
    $this->deleteEntity($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure administrator can delete a business.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testBusiness);
    $this->drupalLogout();
  }

}
