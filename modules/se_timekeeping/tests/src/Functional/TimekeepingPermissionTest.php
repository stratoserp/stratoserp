<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Timekeeping permission tests.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TimekeepingPermissionTest extends FunctionalTestBase {

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
    '/se/timekeeping-list',
  ];

  /**
   * Test the timekeeping permissions.
   */
  public function testTimekeepingPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
