<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TimekeepingPermissionTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

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
