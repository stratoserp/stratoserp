<?php

declare(strict_types=1);

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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testTimekeepingAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $ticket = $this->addTicket($customer);
    $timekeeping = $this->addTimekeeping($ticket);

    $this->drupalLogout();

  }

  /**
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
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
    $this->deleteNode($test_timekeeping, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete timekeeping either!
    $this->drupalLogin($staff);
    $this->deleteNode($test_timekeeping, FALSE);
    $this->drupalLogout();

  }

}
