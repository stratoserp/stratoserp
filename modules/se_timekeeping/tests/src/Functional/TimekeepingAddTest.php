<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

/**
 * Test adding a timekeeping entry.
 *
 * @covers \Drupal\se_timekeeping\Entity\Timekeeping
 * @uses \Drupal\se_customer\Entity\Customer
 * @uses \Drupal\se_ticket\Entity\Ticket
 * @group se_timekeeping
 * @group stratoserp
 */
class TimekeepingAddTest extends TimekeepingTestBase {

  /**
   * Ensure that timekeeping can be successfully added.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testTimekeepingAdd(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();

    $testTicket = $this->addTicket($testCustomer);
    $this->drupalGet($testTicket->toUrl());
    $content = $this->getTextContent();
    self::assertStringContainsString($testCustomer->getName(), $content);

    $timekeeping = $this->addTimekeeping($testTicket);
    $this->drupalGet($timekeeping->toUrl());
    $content = $this->getTextContent();
    self::assertStringContainsString($testCustomer->getName(), $content);

    $this->drupalLogout();
  }

}
