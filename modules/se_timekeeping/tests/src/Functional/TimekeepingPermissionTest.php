<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

/**
 * Timekeeping permission tests.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingPermissionTest extends TimekeepingTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/se/timekeeping',
  ];

  /**
   * Test the timekeeping permissions.
   */
  public function testTimekeepingPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
