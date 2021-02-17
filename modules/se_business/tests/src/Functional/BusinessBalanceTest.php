<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test getting, setting and ajusting business balance.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessBalanceTest extends FunctionalTestBase {

  /**
   * Test getting, setting and ajusting business balance.
   */
  public function testBusinessBalance(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $business = $this->addBusiness();

    \Drupal::service('se_business.service')->setBalance($business, 25);
    $this->assertEqual(\Drupal::service('se_business.service')->getBalance($business), 25);

    \Drupal::service('se_business.service')->adjustBalance($business, -50);
    $this->assertEqual(\Drupal::service('se_business.service')->getBalance($business), -25);

    $this->drupalLogout();
  }

}
