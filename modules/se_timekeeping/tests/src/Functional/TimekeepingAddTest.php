<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test adding a timekeeping entry.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingAddTest extends FunctionalTestBase {

  /**
   * Ensure that timekeeping can be successfully added.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTimekeepingAdd(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();
    $testTicket = $this->addTicket($testCustomer);
    $this->addTimekeeping($testTicket);
    $this->drupalLogout();
  }

}
