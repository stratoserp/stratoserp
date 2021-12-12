<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription_invoice\Functional;

use Drupal\se_subscription\Entity\Subscription;
use Drupal\Tests\se_subscription\Functional\SubscriptionTestBase;

class SubscriptionInvoiceTest extends SubscriptionTestBase {

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
      'se_su_lines' => [
        $item,
      ],
      'se_su_next_due' => [
        $oldTime
      ],
      'se_su_period' => [['duration' => 'P1Y']]
    ]);
    $subscription->save();
    $this->markEntityForCleanup($subscription);
    $id = $subscription->id();
    $oldCount = \Drupal::entityTypeManager()->getStorage('se_invoice')->loadByProperties([]);

    // $invoices = \Drupal::service('se_subscription_invoice')->processSubscriptions();
    $invoices = \Drupal::service('se_subscription_invoice')->processDateSubscriptions();

    $updated = Subscription::load($id);
    $newTime = $updated->se_su_next_due->value;
    $newCount = \Drupal::entityTypeManager()->getStorage('se_invoice')->loadByProperties([]);

    self::assertNotEquals($oldTime, $newTime);
    self::assertNotEquals($oldCount, $newCount);
  }


}
