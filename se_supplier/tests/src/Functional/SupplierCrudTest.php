<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierCrudTest extends FunctionalTestBase {

  protected $staff;

  /**
   *
   */
  public function testSupplierAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->addSupplier();

    $this->drupalLogout();
  }

  /**
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testSupplierDelete(): void {
    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a contact for testing.
    $this->drupalLogin($staff);
    $test_supplier = $this->addSupplier();
    $this->drupalLogout();

    // Ensure customer can't delete customers.
    $this->drupalLogin($customer);
    $this->deleteNode($test_supplier, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete customers.
    $this->drupalLogin($staff);
    $this->deleteNode($test_supplier, FALSE);
    $this->drupalLogout();

  }

}
