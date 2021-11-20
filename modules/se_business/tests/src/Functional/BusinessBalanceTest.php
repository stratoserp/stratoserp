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

    \Drupal::service('se_business.service')->setBalance($business, 25017);
    self::assertEquals(25017, \Drupal::service('se_business.service')->getBalance($business));

    \Drupal::service('se_business.service')->adjustBalance($business, -5012);
    self::assertEquals(20005, \Drupal::service('se_business.service')->getBalance($business));

    \Drupal::service('se_business.service')->adjustBalance($business, -20005);
    self::assertEquals(0, \Drupal::service('se_business.service')->getBalance($business));

    $this->drupalLogout();
  }

}
