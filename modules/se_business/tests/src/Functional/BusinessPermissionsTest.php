<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

/**
 * Test basic permissions.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessPermissionsTest extends BusinessTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/business/add',
    '/se/business-list',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testBusinessPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
