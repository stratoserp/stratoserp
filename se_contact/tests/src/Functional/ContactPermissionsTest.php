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
    '/se/contact-list',
  ];

  public function testContactPermissions() {
    $this->basicPermissionCheck($this->pages);
  }

}
