<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Ticket CRUD tests.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketCrudTest extends FunctionalTestBase {

  /**
   * Ensure that a ticket can be successfully added.
   */
  public function testTicketAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();
    $ticket = $this->addTicket($business);

    $this->drupalLogout();

  }

  /**
   * Ensure that ticket access levels are correct.
   */
  public function testTicketDelete(): void {

    $staff = $this->setupStaffUser();
    $business = $this->setupBusinessUser();

    // Create a business for testing.
    $this->drupalLogin($staff);
    $test_business = $this->addBusiness();
    $test_ticket = $this->addTicket($test_business);
    $this->drupalLogout();

    // Ensure business can't delete tickets.
    $this->drupalLogin($business);
    $this->deleteNode($test_ticket, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete tickets either!
    $this->drupalLogin($staff);
    $this->deleteNode($test_ticket, FALSE);
    $this->drupalLogout();

  }

}
