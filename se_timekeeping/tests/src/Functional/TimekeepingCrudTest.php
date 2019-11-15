<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingCrudTest extends FunctionalTestBase {
  protected $staff;

  /**
   * Ensure that timekeeping can be successfully added.
   *
   */
  public function testTimekeepingAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $test_ticket = $this->addTicket($test_customer);
    $test_timekeeping = $this->addTimekeeping($test_ticket);
    $this->drupalLogout();

  }

  /**
   *
   */
  public function testTimekeepingDelete(): void {

    $staff = $this->setupStaffUser();
    $customer = $this->setupCustomerUser();

    // Create a customer for testing.
    $this->drupalLogin($staff);
    $test_customer = $this->addCustomer();
    $test_ticket = $this->addTicket($test_customer);
    $test_timekeeping = $this->addTimekeeping($test_ticket);
    $this->drupalLogout();

    // Ensure customer can't delete timekeeping.
    $this->drupalLogin($customer);
    $this->deleteTimekeeping($test_timekeeping, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete timekeeping either!
    $this->drupalLogin($staff);
    $this->deleteTimekeeping($test_timekeeping, FALSE);
    $this->drupalLogout();

  }

}
