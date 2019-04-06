<?php

namespace Drupal\Tests\se_customer\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerPermissionsTest extends ExistingSiteBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_customer',
  ];

  public function testCustomerPermissions() {
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