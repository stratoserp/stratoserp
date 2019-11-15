<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
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

    $test_customer = $this->addCustomer();
    $this->addContact($test_customer);

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
    $test_customer = $this->addCustomer();
    $test_contact = $this->addContact($test_customer);
    $this->drupalLogout();

    // Ensure customer can't delete contacts.
    $this->drupalLogin($customer);
    $this->deleteNode($test_contact, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($staff);
    $this->deleteNode($test_contact, TRUE);
    $this->drupalLogout();

  }

}
