<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

/**
 * Ticket permission tests.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketPermissionTest extends TicketTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
    '/ticket/add',
    '/se/tickets',
  ];

  /**
   * Test the ticket permissions.
   */
  public function testTicketPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
