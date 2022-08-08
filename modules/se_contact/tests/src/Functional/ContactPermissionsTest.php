<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

/**
 * Test Contact Permiossions.
 *
 * @covers \Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactPermissionsTest extends ContactTestBase {

  /**
   * List of pages to check permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/contact/add',
    '/se/customers/contact-list',
  ];

  /**
   * Test the basic contact permissions for the default pages.
   */
  public function testContactPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
