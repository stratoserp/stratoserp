<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Functional;

/**
 * Test subscription integration with business.
 */
class SubscriptionBusinessIntegrationTest extends SubscriptionTestBase {

  /**
   * Ensure the subcriptions vertical tab shows.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testSubscriptionAddBusiness(): void {
    $this->drupalLogin($this->staff);
    $this->businessFakerSetup();
    $customer = $this->addBusiness();
    $this->drupalGet($customer->toUrl());
    $content = $this->getTextContent();
    self::assertStringContainsString('Subscriptions', $content);

    $this->drupalLogout();
  }

}
