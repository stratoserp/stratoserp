<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

/**
 * Test Contact Add/Delete.
 *
 * @covers \Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactCrudTest extends ContactTestBase {

  /**
   * Add a contact as staff.
   */
  public function testContactAdd(): void {
    // First setup a customer to add the contact to.
    $this->drupalLogin($this->staff);
    $customer = $this->addCustomer();
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addContact($customer);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addContact($customer);
    $this->drupalLogout();
  }

  /**
   * Try and delete a contact as staff and customer.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactDelete(): void {
    // Create a contact for testing.
    $this->drupalLogin($this->staff);
    $this->customerFakerSetup();
    $testCustomer = $this->addCustomer();
    $testContact = $this->addContact($testCustomer);
    $this->drupalLogout();

    // Ensure customers can't delete contacts.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testContact, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete contacts.
    $this->drupalLogin($this->staff);
    $this->deleteEntity($testContact, FALSE);
    $this->drupalLogout();

    // Ensure owner can delete contacts.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testContact);
    $this->drupalLogout();
  }

}
