<?php

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\ContactTestBase;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 *
 */
class ContactPermissionsTest extends ContactTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_contact',
    '/se/contact-list'
  ];

  public function testContactPermissions() {
    $this->customer = $this->createUser([], NULL, FALSE);
    $this->customer->addRole('customer');
    $this->customer->save();

    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();

    foreach ($this->pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->customer);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
      $this->drupalLogout();
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->staff);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
      $this->drupalLogout();
    }
  }


}
