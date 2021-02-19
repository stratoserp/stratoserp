<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

/**
 * Test Contact Permiossions.
 *
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactPermissionsTest extends ContactTestBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * List of pages to check permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/contact/add',
    '/se/contact-list',
  ];

  /**
   * Test the basic contact permissions for the default pages.
   */
  public function testContactPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
