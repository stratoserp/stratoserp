<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Functional;

/**
 * Test subscription integration with customer.
 */
class SubscriptionCustomerIntegrationTest extends SubscriptionTestBase {

  /**
   * Ensure the Subcriptions vertical tab shows.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testSubscriptionAddCustomer(): void {
    $this->drupalLogin($this->staff);
    $this->customerFakerSetup();
    $customer = $this->addCustomer();
    $this->drupalGet($customer->toUrl());
    $content = $this->getTextContent();
    self::assertStringContainsString('Subscriptions', $content);

    $this->drupalLogout();
  }

}
