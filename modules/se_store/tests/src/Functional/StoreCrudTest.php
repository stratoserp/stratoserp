<?php

declare(strict_types=1);

namespace Drupal\Tests\se_store\Functional;

/**
 * Test Store Add/Delete.
 *
 * @covers \Drupal\se_store
 * @group se_store
 * @group stratoserp
 */
class StoreCrudTest extends StoreTestBase {

  /**
   * Add a store as staff.
   */
  public function testStoreAdd(): void {
    // First setup a customer to add the store to.
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $testSupplier = $this->addSupplier();
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addStore($testCustomer, $testSupplier);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addStore($testCustomer, $testSupplier);
    $this->drupalLogout();
  }

  /**
   * Try and delete a store as staff and customer.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStoreDelete(): void {
    // Create a store for testing.
    $this->drupalLogin($this->owner);
    $this->customerFakerSetup();
    $testCustomer = $this->addCustomer();
    $testSupplier = $this->addSupplier();
    $testStore = $this->addStore($testCustomer, $testSupplier);
    $this->drupalLogout();

    // Ensure customers can't delete stores.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testStore, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete stores.
    $this->drupalLogin($this->staff);
    $this->deleteEntity($testStore, FALSE);
    $this->drupalLogout();

    // Ensure owner can delete stores.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testStore);
    $this->drupalLogout();
  }

}
