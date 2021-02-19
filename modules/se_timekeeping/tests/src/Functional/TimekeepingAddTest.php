<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_ticket\Traits\TicketTestTrait;

/**
 * Test adding a timekeeping entry.
 *
 * @coversDefault Drupal\se_timekeeping
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingAddTest extends TimekeepingTestBase {

  use TicketTestTrait;

  /**
   * Ensure that timekeeping can be successfully added.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTimekeepingAdd(): void {
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $testTicket = $this->addTicket($testBusiness);
    $this->addTimekeeping($testTicket);
    $this->drupalLogout();
  }

}
