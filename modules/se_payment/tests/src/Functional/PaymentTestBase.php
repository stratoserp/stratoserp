<?php

declare(strict_types=1);

namespace Drupal\Tests\se_payment\Functional;

use Drupal\se_payment\Entity\Payment;
use Drupal\Tests\se_payment\Traits\PaymentTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic contact tests.
 */
class PaymentTestBase extends FunctionalTestBase {

  use PaymentTestTrait;

  /**
   * Faker factory for payment.
   *
   * @var \Faker\Factory
   */
  protected $payment;

  /**
   * Setup the basic contact tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->paymentFakerSetup();
  }

  /**
   * Our own teardown to delete payments first.
   */
  public function tearDown(): void {
    foreach ($this->cleanupEntities as $index => $entity) {
      if ($entity instanceof Payment) {
        $entity->delete();
        unset($this->cleanupEntities[$index]);
      }
    }

    // Now its safe to let the standard teardown run.
    parent::tearDown();
  }

}
