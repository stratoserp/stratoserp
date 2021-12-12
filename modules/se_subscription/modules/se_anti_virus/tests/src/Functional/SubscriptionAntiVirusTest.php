<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription_invoice\Functional;

use Drupal\se_subscription\Entity\Subscription;
use Drupal\Tests\se_subscription\Functional\SubscriptionTestBase;

class SubscriptionAntiVirusTest extends SubscriptionTestBase {

  /**
   * Ensure that adding an Anti Virus subscrioption works.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testSubscriptionAntiVirus(): void {
    $this->drupalLogin($this->staff);
    $customer = $this->addBusiness();
    $supplier = $this->addBusiness('Supplier');
    $item = $this->addRecurringItem();

    $this->addSubscription('se_anti_virus', $customer, $supplier, $item);

    // @todo - Need better checks here, then clone/adjust for each type.
    $this->drupalGet($customer->toUrl());

    $content = $this->getTextContent();
    self::assertStringContainsString('Subscriptions', $content);

    $this->drupalLogout();
  }
}
