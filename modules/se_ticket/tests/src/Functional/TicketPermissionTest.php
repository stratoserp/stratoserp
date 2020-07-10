<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketPermissionTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_ticket',
    '/se/ticket-list',
  ];

  /**
   * Test the ticket permissions.
   */
  public function testTicketPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
