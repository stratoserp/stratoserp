<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_item\Entity\Item;
use Drupal\se_subscription\Entity\Subscription;
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
   * Add a subscription and set the business to the value passed in.
   *
   * @param \Drupal\se_business\Entity\Business|null $customer
   *   The business to associate the subscription with.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_subscription\Entity\subscription|null
   *   The subscription to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addSubscription(string $subscriptionType, Business $customer, Business $supplier, Item $item, bool $allowed = TRUE) {
    if (!isset($this->subscriptionName)) {
      $this->subscriptionFakerSetup();
    }

    /** @var \Drupal\se_subscription\Entity\subscription $subscription */
    $subscription = $this->createSubscription([
      'type' => $subscriptionType,
      'name' => $this->subscriptionName,
      'se_bu_ref' => $customer,
      'se_su_ref' => $supplier,
      'se_item_lines' => [
        $item,
      ]
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
