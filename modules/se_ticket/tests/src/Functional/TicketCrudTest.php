<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketCrudTest extends FunctionalTestBase {
  protected $staff;

  /**
   * Ensure that a ticket can be successfully added.
   *
   */
  public function testTicketAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $ticket = $this->addTicket($customer);

    $this->drupalLogout();

  }

  /**
   * Ensure that ticket access levels are correct.
   *
   */
  public function testTicketDelete(): void {

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a customer for testing.
    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $test_ticket = $this->addTicket($test_customer);
    $this->drupalLogout();

    // Ensure customer can't delete tickets.
    $this->drupalLogin($customer);
    $this->deleteNode($test_ticket, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete tickets either!
    $this->drupalLogin($staff);
    $this->deleteNode($test_ticket, FALSE);
    $this->drupalLogout();

  }

}
