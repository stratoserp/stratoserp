<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

/**
 * Test getting, setting and adjusting business outstanding balance.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessBalanceTest extends BusinessTestBase {

  /**
   * Test getting, setting and adjusting business balance.
   */
  public function testBusinessBalance(): void {

    $this->drupalLogin($this->staff);
    $business = $this->addBusiness();

    $business->setBalance(25017);
    self::assertEquals(25017, $business->getBalance());

    $business->adjustBalance(-5012);
    self::assertEquals(20005, $business->getBalance());

    $business->adjustBalance(-20005);
    self::assertEquals(0, $business->getBalance());

    $this->drupalLogout();
  }

}
