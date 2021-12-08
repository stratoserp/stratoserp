<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Ticket entity.
 */
class SubscriptionTestBase extends FunctionalTestBase {

  use BusinessTestTrait;

  /**
   * Storage for the faker data for ticket.
   *
   * @var \Faker\Factory
   */
  protected $subscription;

  /**
   * Basic setup.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->subscriptionFakerSetup();
  }

}
