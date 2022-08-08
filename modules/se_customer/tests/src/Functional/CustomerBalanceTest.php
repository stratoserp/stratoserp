<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

/**
 * Test getting, setting and adjusting customer outstanding balance.
 *
 * @covers \Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerBalanceTest extends CustomerTestBase {

  /**
   * Test getting, setting and adjusting customer balance.
   */
  public function testCustomerBalance(): void {

    $this->drupalLogin($this->staff);
    $customer = $this->addCustomer();

    $customer->setBalance(25017);
    self::assertEquals(25017, $customer->getBalance());

    $customer->adjustBalance(-5012);
    self::assertEquals(20005, $customer->getBalance());

    $customer->adjustBalance(-20005);
    self::assertEquals(0, $customer->getBalance());

    $this->drupalLogout();
  }

}
