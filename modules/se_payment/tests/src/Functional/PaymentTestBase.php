<?php

declare(strict_types=1);

namespace Drupal\Tests\se_payment\Functional;

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

}
