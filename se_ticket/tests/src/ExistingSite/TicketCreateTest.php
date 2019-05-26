<?php

namespace Drupal\Tests\se_ticket\ExistingSite;

/**
 * @coversDefault Drupal\se_customer
 * @group se_ticket
 * @group stratoserp
 *
 */
class TicketCreateTest extends TicketTestBase {
  protected $staff;

  public function testTicketAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $ticket = $this->addTicket();

    $this->drupalLogout();

  }

}