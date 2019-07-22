<?php

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_testing\Functional\TimekeepingTestBase;

/**
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingCrudTest extends TimekeepingTestBase {
  protected $staff;

  /**
   * Ensure that timekeeping can be successfully added.
   */
  public function testTimekeepingAdd() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $ticket = $this->addTicket($customer);
    $timekeeping = $this->addTimekeeping($ticket);

    $this->drupalLogout();

  }

  /**
   *
   */
  public function testTimekeepingDelete() {

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a customer for testing.
    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $ticket = $this->addTicket($test_customer);
    $timekeeping = $this->addTimekeeping($ticket);
    $this->drupalLogout();

    // Ensure customer can't delete timekeeping.
    $this->drupalLogin($customer);
    $this->deleteNode($timekeeping, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete timekeeping either!
    $this->drupalLogin($staff);
    $this->deleteNode($timekeeping, FALSE);
    $this->drupalLogout();

  }

}
