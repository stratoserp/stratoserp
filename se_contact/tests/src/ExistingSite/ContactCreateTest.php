<?php

namespace Drupal\Tests\se_contact\ExistingSite;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 *
 */
class ContactCreateTest extends ContactTestBase {

  protected $staff;

  public function testContactAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $contact = $this->addContact();

    $this->drupalLogout();
  }

}

