<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

/**
 * Test various Supplier crud operations.
 *
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierCrudTest extends SupplierTestBase {

  /**
   * Add a Supplier.
   */
  public function testSupplierAdd(): void {
    $this->drupalLogin($this->staff);
    $this->supplierFakerSetup();
    $this->addSupplier();
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->supplierFakerSetup();
    $this->addSupplier();
    $this->drupalLogout();
  }

  /**
   * Edit a supplier.
   */
  public function testSupplierEdit(): void {
    // Create a supplier for testing.
    $this->drupalLogin($this->staff);
    $testSupplier = $this->addSupplier();
    $this->drupalLogout();

    // Ensure customers can't edit a supplier.
    $this->drupalLogin($this->customer);
    $this->editEntity($testSupplier, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a supplier.
    $this->drupalLogin($this->staff);
    $this->editEntity($testSupplier);
    $this->drupalLogout();

    // Ensure administrator can delete a supplier.
    $this->drupalLogin($this->owner);
    $this->editEntity($testSupplier);
    $this->drupalLogout();
  }

  /**
   * Delete a supplier.
   */
  public function testSupplierDelete(): void {
    // Create a supplier for testing.
    $this->drupalLogin($this->staff);
    $testSupplier = $this->addSupplier();
    $this->drupalLogout();

    // Ensure customers can't edit a supplier.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testSupplier, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete a supplier.
    $this->drupalLogin($this->staff);
    $this->deleteEntity($testSupplier, FALSE);
    $this->drupalLogout();

    // Ensure administrator can delete a supplier.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testSupplier);
    $this->drupalLogout();
  }

}
