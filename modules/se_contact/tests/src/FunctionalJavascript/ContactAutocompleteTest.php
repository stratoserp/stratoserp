<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\FunctionalJavascript;

use Drupal\Tests\se_testing\FunctionalJavascript\FunctionalJavascriptTestBase;

/**
 * Test Contact autocomplete.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactAutocompleteTest extends FunctionalJavascriptTestBase {

  /**
   * Test contact search.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactSearch(): void {

    $staff = $this->setupStaffUser();

    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();
    $this->addContact($testCustomer);
    $this->drupalLogout();

    // Make a new staff user and test they can find the contact.
    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $this->drupalGet('<front>');

    $this->getCurrentPage();
  }

}
