<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test getting, setting and adjusting business outstanding balance.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessBalanceTest extends FunctionalTestBase {

  use BusinessTestTrait;

  /**
   * Test getting, setting and adjusting business balance.
   */
  public function testBusinessBalance(): void {

    $this->businessFakerSetup();

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $business = $this->addBusiness();

    \Drupal::service('se_business.service')->setBalance($business, 25);
    self::assertEquals(25, \Drupal::service('se_business.service')->getBalance($business));

    \Drupal::service('se_business.service')->adjustBalance($business, -50);
    self::assertEquals(-25, \Drupal::service('se_business.service')->getBalance($business));

    $this->drupalLogout();
  }

}
