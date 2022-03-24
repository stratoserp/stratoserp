<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\FunctionalJavascript;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_contact\Traits\ContactTestTrait;
use Drupal\Tests\se_testing\FunctionalJavascript\FunctionalJavascriptTestBase;

/**
 * Test Contact autocomplete.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactAutocompleteTest extends FunctionalJavascriptTestBase {

  use CustomerTestTrait;
  use ContactTestTrait;

  /**
   * Test contact search.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContactSearch(): void {

    $this->drupalLogin($this->staff);
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
