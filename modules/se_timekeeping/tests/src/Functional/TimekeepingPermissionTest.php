<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

/**
 * Timekeeping permission tests.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingPermissionTest extends TimekeepingTestBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/se/timekeeping-list',
  ];

  /**
   * Test the timekeeping permissions.
   */
  public function testTimekeepingPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
