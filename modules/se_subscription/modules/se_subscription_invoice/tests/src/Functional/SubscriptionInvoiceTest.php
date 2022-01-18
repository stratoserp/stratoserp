<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription_invoice\Functional;

use Drupal\se_subscription\Entity\Subscription;
use Drupal\Tests\se_subscription\Functional\SubscriptionTestBase;

/**
 * Test for creating subscriptions and invoicing them.
 */
class SubscriptionInvoiceTest extends SubscriptionTestBase {

  /**
   * Test creating subscriptions and invoicing them.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSubscriptionInvoice(): void {
    $this->drupalLogin($this->staff);
    $customer = $this->addBusiness();
    $supplier = $this->addBusiness('Supplier');
    $item = $this->addRecurringItem('P1Y');

    $this->subscriptionFakerSetup();

    // Create a subscription at least a year old.
    $oldTime = strtotime('1 year ago');
    $subscription = $this->createSubscriptionContent([
      'type' => 'se_anti_virus',
      'name' => $this->subscriptionName,
      'se_bu_ref' => $customer,
      'se_su_ref' => $supplier,
      'se_item_lines' => [
        $item,
      ],
      'se_next_due' => [
        $oldTime,
      ],
      'se_period' => [
        [
          'duration' => 'P1Y',
        ],
      ],
    ]);
    $subscription->save();
    $this->markEntityForCleanup($subscription);
    $sub = $subscription->id();
    $oldCount = \Drupal::entityTypeManager()->getStorage('se_invoice')->loadByProperties([]);

    $invoices = \Drupal::service('se_subscription_invoice')->processSubscriptions();
    $invoices = \Drupal::service('se_subscription_invoice')->processDateSubscriptions();

    $updated = Subscription::load($sub);
    $newTime = $updated->se_next_due->value;
    $newCount = \Drupal::entityTypeManager()->getStorage('se_invoice')->loadByProperties([]);

    self::assertNotEquals($oldTime, $newTime);
    self::assertNotEquals($oldCount, $newCount);
  }

}
