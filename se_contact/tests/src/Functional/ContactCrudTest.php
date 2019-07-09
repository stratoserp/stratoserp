<?php

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\ContactTestBase;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 *
 */
class ContactCrudTest extends ContactTestBase {

  protected $staff;

  public function testContactAdd() {

    $this->contactFakerSetup();
    $this->customerFakerSetup();

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->addContact();

    $this->drupalLogout();
  }

  public function testContactDelete() {

    $this->contactFakerSetup();
    $this->customerFakerSetup();

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a contact for testing.
    $this->drupalLogin($staff);
    $contact = $this->addContact();
    $this->drupalLogout();

    // Ensure customer can't delete contacts.
    $this->drupalLogin($customer);
    $this->deleteContact($contact, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($staff);
    $this->deleteContact($contact, TRUE);
    $this->drupalLogout();

  }

}

