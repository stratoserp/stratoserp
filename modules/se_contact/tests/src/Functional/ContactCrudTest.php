<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

/**
 * Test Contact Add/Delete.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactCrudTest extends ContactTestBase {

  /**
   * Add a contact as staff.
   */
  public function testContactAdd(): void {
    // First setup a business to add the contact to.
    $this->drupalLogin($this->staff);
    $business = $this->addBusiness();
    $this->drupalLogout();

    $this->drupalLogin($this->customer);
    $this->addContact($business, FALSE);
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addContact($business);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addContact($business);
    $this->drupalLogout();
  }

  /**
   * Try and delete a contact as staff and business.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactDelete(): void {
    // Create a contact for testing.
    $this->drupalLogin($this->staff);
    $this->businessFakerSetup();
    $testBusiness = $this->addBusiness();
    $testContact = $this->addContact($testBusiness);
    $this->drupalLogout();

    // Ensure customers can't delete contacts.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testContact, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete contacts.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testContact, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testContact, TRUE);
    $this->drupalLogout();
  }

}
