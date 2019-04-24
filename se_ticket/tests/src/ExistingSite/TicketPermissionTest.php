<?php

namespace Drupal\Tests\se_ticket\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * @coversDefault Drupal\se_goods_receipt
 * @group se_contact
 * @group stratoserp
 *
 */
class TicketPermissionTest extends ExistingSiteBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_goods_receipt',
  ];

  public function testTicketPermissions() {
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
