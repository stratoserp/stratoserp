<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Contact Permiossions.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactPermissionsTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_contact',
    '/se/contact-list',
  ];

  /**
   * Test the basic contact permissions for the default pages.
   */
  public function testContactPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
