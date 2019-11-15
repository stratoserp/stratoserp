<?php


declare(strict_types=1);

namespace Drupal\Tests\se_contact\FunctionalJavascript;

use Drupal\Tests\se_testing\FunctionalJavascript\FunctionalJavascriptTestBase;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactAutocompleteTest extends FunctionalJavascriptTestBase {

  public function testContactSearch(): void {

    $staff = $this->setupStaffUser();

    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $test_contact = $this->addContact($test_customer);
    $this->drupalLogout();

    // Make a new staff user and test they can find the contact.
    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $this->drupalGet('/');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPage();

  }

}
