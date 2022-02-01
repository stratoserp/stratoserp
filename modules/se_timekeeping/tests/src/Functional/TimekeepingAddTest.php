<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

/**
 * Test adding a timekeeping entry.
 *
 * @coversDefault Drupal\se_timekeeping
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
    $testBusiness = $this->addBusiness();

    $testTicket = $this->addTicket($testBusiness);
    $this->drupalGet($testTicket->toUrl());
    $content = $this->getTextContent();
    self::assertStringContainsString($testBusiness->getName(), $content);

    $timekeeping = $this->addTimekeeping($testTicket);
    $this->drupalGet($timekeeping->toUrl());
    $content = $this->getTextContent();
    self::assertStringContainsString($testBusiness->getName(), $content);

    $this->drupalLogout();
  }

}
