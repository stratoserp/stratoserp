<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test deleting a timekeeping entry.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingDeleteTest extends FunctionalTestBase {

  /**
   * Test timekeeping delete.
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
    $testCustomer = $this->addCustomer();
    $testTicket = $this->addTicket($testCustomer);
    $testTimekeeping = $this->addTimekeeping($testTicket);
    $this->drupalLogout();

    // Ensure customer can't delete timekeeping.
    $this->drupalLogin($customer);
    $this->deleteTimekeeping($testTimekeeping, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete timekeeping either!
    $this->drupalLogin($staff);
    $this->deleteTimekeeping($testTimekeeping, FALSE);
    $this->drupalLogout();

  }

}
