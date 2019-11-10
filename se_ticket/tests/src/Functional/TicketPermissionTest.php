<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_testing\Functional\TicketTestBase;

/**
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketPermissionTest extends TicketTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_ticket',
    '/se/ticket-list',
  ];

  /**
   * Test the ticket permissions.
   */
  public function testTicketPermissions() {
    $this->basicPermissionCheck($this->pages);
  }

}
