<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Contact Add/Delete.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactCrudTest extends FunctionalTestBase {

  /**
   * Add a customer as staff.
   */
  public function testContactAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $this->addContact($customer);

    $this->drupalLogout();
  }

  /**
   * Try and delete a contact as staff and customer.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testContactDelete(): void {

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a contact for testing.
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();
    $testContact = $this->addContact($testCustomer);
    $this->drupalLogout();

    // Ensure customer can't delete contacts.
    $this->drupalLogin($customer);
    $this->deleteNode($testContact, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($staff);
    $this->deleteNode($testContact, TRUE);
    $this->drupalLogout();
  }

}
