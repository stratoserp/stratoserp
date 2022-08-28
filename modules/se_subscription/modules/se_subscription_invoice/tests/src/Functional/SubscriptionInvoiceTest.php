<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription_invoice\Functional;

use Drupal\se_subscription\Entity\Subscription;
use Drupal\Tests\se_subscription\Functional\SubscriptionTestBase;

/**
 * Test for creating subscriptions and invoicing them.
 *
 * @covers \Drupal\se_subscription_invoice\Service\SubscriptionInvoiceService
 * @uses \Drupal\se_subscription
 * @uses \Drupal\se_customer\Entity\Customer
 * @uses \Drupal\se_invoice\Entity\Invoice
 * @uses \Drupal\se_item\Entity\Item
 * @uses \Drupal\se_supplier\Entity\Supplier
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
  public function testDateSubscriptionsInvoice(): void {
    $etm = \Drupal::entityTypeManager();

    $this->drupalLogin($this->staff);
    $customer = $this->addCustomer();
    $supplier = $this->addSupplier();
    $item = $this->addRecurringItem('P1Y');

    $this->subscriptionFakerSetup();

    // Create a subscription at least a year old.
    $oldTime = strtotime('1 year ago');
    $subscription = $this->createSubscriptionContent([
      'type' => 'se_anti_virus',
      'name' => $this->subscriptionName,
      'se_cu_ref' => $customer,
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
    $oldCount = $etm->getStorage('se_invoice')
      ->getQuery()
      ->count()
      ->execute();

    \Drupal::service('se_subscription_invoice')->processSubscriptions($customer->id());

    $newCount = $etm->getStorage('se_invoice')
      ->getQuery()
      ->count()
      ->execute();

    // There should be no non-date ones in this test.
    self::assertEquals($oldCount, $newCount);

    \Drupal::service('se_subscription_invoice')->processDateSubscriptions($customer->id());

    $updated = Subscription::load($sub);
    $newTime = $updated->se_next_due->value;

    $newCount = $etm->getStorage('se_invoice')
      ->getQuery()
      ->count()
      ->execute();

    self::assertNotEquals($oldTime, $newTime);
    self::assertNotEquals($oldCount, $newCount);
  }

}
