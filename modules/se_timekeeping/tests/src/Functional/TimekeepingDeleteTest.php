<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

/**
 * Test deleting a timekeeping entry.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingDeleteTest extends TimekeepingTestBase {

  /**
   * Test timekeeping delete.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTimekeepingDelete(): void {

    // Create a business for testing.
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $testTicket = $this->addTicket($testBusiness);
    $testTimekeeping = $this->addTimekeeping($testTicket);
    $this->drupalLogout();

    // Ensure business can't delete timekeeping.
    $this->drupalLogin($this->customer);
    $this->deleteTimekeeping($testTimekeeping, FALSE);
    $this->drupalLogout();

    // Ensure staff can't delete timekeeping either!
    $this->drupalLogin($this->staff);
    $this->deleteTimekeeping($testTimekeeping, FALSE);
    $this->drupalLogout();

  }

}
