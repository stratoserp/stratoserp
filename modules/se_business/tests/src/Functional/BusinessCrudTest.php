<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test various Business crud operations.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessCrudTest extends FunctionalTestBase {

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

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->addBusiness();

    $this->drupalLogout();
  }

  /**
   * Delete a business.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBusinessDelete(): void {
    $staff = $this->setupStaffUser();
    $business = $this->setupBusinessUser();

    // Create a business for testing.
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();
    $this->drupalLogout();

    // Ensure business can't delete businesss.
    $this->drupalLogin($business);
    $this->deleteBusiness($testBusiness, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete businesss.
    $this->drupalLogin($staff);
    $this->deleteBusiness($testBusiness, TRUE);
    $this->drupalLogout();

  }

}
