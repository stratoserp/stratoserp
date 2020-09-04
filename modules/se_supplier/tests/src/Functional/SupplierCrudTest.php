<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test various Supplier crud operations.
 *
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierCrudTest extends FunctionalTestBase {

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * Add a Supplier.
   */
  public function testSupplierAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->addSupplier();

    $this->drupalLogout();
  }

  /**
   * Delete a supplier.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException|\Drupal\Core\Entity\EntityStorageException
   */
  public function testSupplierDelete(): void {
    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a supplier for testing.
    $this->drupalLogin($staff);
    $testSupplier = $this->addSupplier();
    $this->drupalLogout();

    // Ensure customer can't delete suppliers.
    $this->drupalLogin($customer);
    $this->deleteNode($testSupplier, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete suppliers.
    $this->drupalLogin($staff);
    $this->deleteNode($testSupplier, FALSE);
    $this->drupalLogout();

  }

}
