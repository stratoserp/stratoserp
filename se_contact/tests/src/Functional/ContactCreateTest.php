<?php

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\ContactTestBase;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 *
 */
class ContactCreateTest extends ContactTestBase {

  protected $staff;

  public function testContactAdd() {

    $this->contactFakerSetup();
    $this->customerFakerSetup();

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $contact = $this->addContact();

    $this->drupalLogout();
  }

}

