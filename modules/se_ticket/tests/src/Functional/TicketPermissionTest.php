<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Ticket permission tests.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketPermissionTest extends FunctionalTestBase {

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
    '/ticket/add',
    '/se/ticket-list',
  ];

  /**
   * Test the ticket permissions.
   */
  public function testTicketPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
