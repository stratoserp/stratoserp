<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

/**
 * Ticket CRUD tests.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketCrudTest extends TicketTestBase {

  /**
   * Ensure that a ticket can be successfully added.
   */
  public function testTicketAdd(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addTicket($testCustomer);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addTicket($testCustomer);
    $this->drupalLogout();
  }

  /**
   * Ensure that ticket access levels are correct.
   */
  public function testTicketDelete(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $testTicket = $this->addTicket($testCustomer);
    $this->drupalLogout();

    // Ensure customers can't delete tickets.
    $this->drupalLogin($this->customer);
    $this->deleteEntity($testTicket, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete tickets.
    $this->drupalLogin($this->staff);
    $this->deleteEntity($testTicket, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete tickets either!
    $this->drupalLogin($this->owner);
    $this->deleteEntity($testTicket);
    $this->drupalLogout();
  }

}
