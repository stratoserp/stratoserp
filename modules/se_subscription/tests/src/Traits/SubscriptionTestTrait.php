<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_item\Entity\Item;
use Drupal\se_subscription\Entity\Subscription;
use Drupal\se_supplier\Entity\Supplier;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait SubscriptionTestTrait {

  protected string $subscriptionName;
  protected User $subscriptionUser;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function subscriptionFakerSetup(): void {
    $this->faker = Factory::create();

    $this->subscriptionName = $this->faker->text(45);
  }

  /**
   * Add a subscription and set the customer to the value passed in.
   *
   * @param string $subscriptionType
   *   The subscription type we're wrangling.
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to associate the subscription with.
   * @param \Drupal\se_customer\Entity\Supplier $supplier
   *   The supplier to associate the subscription with.
   * @param \Drupal\se_item\Entity\Item $item
   *   The subscription item.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_subscription\Entity\subscription|null
   *   The subscription to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addSubscription(string $subscriptionType, Customer $customer, Supplier $supplier, Item $item, bool $allowed = TRUE) {
    if (!isset($this->subscriptionName)) {
      $this->subscriptionFakerSetup();
    }

    $line = [
      'target_type' => 'se_item',
      'target_id' => $item->id(),
      'quantity' => 1,
      'price' => $item->se_sell_price->value,
      'cost' => $item->se_cost_price->value,
    ];
    $lines[] = $line;

    /** @var \Drupal\se_subscription\Entity\subscription $subscription */
    $subscription = $this->createSubscription([
      'type' => $subscriptionType,
      'name' => $this->subscriptionName,
      'se_cu_ref' => $customer,
      'se_su_ref' => $supplier,
      'se_item_lines' => $lines,
    ]);
    self::assertNotEquals($subscription, FALSE);

    $this->drupalGet($subscription->toUrl());

    $content = $this->getTextContent();

    if (!$allowed) {
      // Equivalent to 403 status.
      self::assertStringContainsString('Access denied', $content);
      return NULL;
    }

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->subscriptionName, $content);

    return $subscription;
  }

  /**
   * Create and save a subscription entity.
   *
   * @param array $settings
   *   Array of settings to apply to the subscription entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created subscription entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createSubscription(array $settings = []) {
    $subscription = $this->createSubscriptionContent($settings);

    $subscription->save();
    $this->markEntityForCleanup($subscription);

    return $subscription;
  }

  /**
   * Create but don't save a subscription entity.
   *
   * @param array $settings
   *   Array of settings to apply to the subscription entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved subscription entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createSubscriptionContent(array $settings = []) {
    if (!array_key_exists('uid', $settings)) {
      if ($this->subscriptionUser = User::load(\Drupal::currentUser()->id())) {
        $settings['uid'] = $this->subscriptionUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->subscriptionUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->subscriptionUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Subscription::create($settings);
  }

}
