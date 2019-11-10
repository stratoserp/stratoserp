<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\ContactTestBase;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactCrudTest extends ContactTestBase {

  protected $staff;

  /**
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $test_customer = $this->addCustomer();
    $this->addContact($test_customer);

    $this->drupalLogout();
  }

  /**
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactDelete(): void {

    $this->contactFakerSetup();
    $this->customerFakerSetup();

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
