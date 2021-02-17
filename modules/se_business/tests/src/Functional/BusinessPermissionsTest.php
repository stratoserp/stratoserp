<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test basic permissions.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessPermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private $pages = [
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
