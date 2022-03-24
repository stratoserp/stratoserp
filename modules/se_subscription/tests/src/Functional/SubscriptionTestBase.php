<?php

declare(strict_types=1);

namespace Drupal\Tests\se_subscription\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_subscription\Traits\SubscriptionTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Ticket entity.
 */
class SubscriptionTestBase extends FunctionalTestBase {

  use CustomerTestTrait;
  use ItemTestTrait;
  use SubscriptionTestTrait;

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
