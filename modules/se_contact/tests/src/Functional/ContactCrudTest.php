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
   * Add a contact as staff.
   */
  public function testContactAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();
    $this->addContact($business);

    $this->drupalLogout();
  }

  /**
   * Try and delete a contact as staff and business.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testContactDelete(): void {

    $staff = $this->setupStaffUser();
    $business = $this->setupBusinessUser();

    // Create a contact for testing.
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();
    $testContact = $this->addContact($testBusiness);
    $this->drupalLogout();

    // Ensure business can't delete contacts.
    $this->drupalLogin($business);
    $this->deleteNode($testContact, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete contacts.
    $this->drupalLogin($staff);
    $this->deleteNode($testContact, TRUE);
    $this->drupalLogout();
  }

}
