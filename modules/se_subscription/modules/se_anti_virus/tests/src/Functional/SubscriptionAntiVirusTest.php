<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Functional;

use Drupal\Tests\se_subscription\Functional\SubscriptionTestBase;

/**
 * Specific subscription type test.
 *
 * @covers \Drupal\se_subscription
 * @uses \Drupal\se_customer\Entity\Customer
 * @uses \Drupal\se_supplier\Entity\Supplier
 */
class SubscriptionAntiVirusTest extends SubscriptionTestBase {

  /**
   * Ensure that adding an Anti Virus subscription works.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testSubscriptionAntiVirus(): void {
    $this->drupalLogin($this->staff);
    $customer = $this->addCustomer();
    $supplier = $this->addSupplier();
    $item = $this->addRecurringItem();

    $this->addSubscription('se_anti_virus', $customer, $supplier, $item);

    // @todo - Need better checks here, then clone/adjust for each type.
    $this->drupalGet($customer->toUrl());

    $content = $this->getTextContent();
    self::assertStringContainsString('Subscriptions', $content);

    $this->drupalLogout();
  }

}
