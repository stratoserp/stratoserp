<?php

namespace Drupal\Tests\se_ticket\Functional;


use Drupal\Tests\se_testing\Functional\TicketTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_ticket
 * @group stratoserp
 *
 */
class TicketCrudTest extends TicketTestBase {
  protected $staff;

  public function testTicketAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $ticket = $this->addTicket($customer);
    $this->deleteTicket($ticket);

    $this->drupalLogout();

  }

  public function testTicketDelete() {

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a customer for testing.
    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $ticket = $this->addTicket($test_customer);
    $this->drupalLogout();

    // Ensure customer can't delete tickets.
    $this->drupalLogin($customer);
    $this->deleteTicket($ticket, FALSE);
    $this->drupalLogout();

    // Ensure staff can delete tickets.
    $this->drupalLogin($staff);
    $this->deleteTicket($ticket, TRUE);
    $this->drupalLogout();

  }

}