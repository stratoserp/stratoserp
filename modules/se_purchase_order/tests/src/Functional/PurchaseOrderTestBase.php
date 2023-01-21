<?php

declare(strict_types=1);

namespace Drupal\Tests\se_purchase_order\Functional;

use Drupal\Tests\se_purchase_order\Traits\PurchaseOrderTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic contact tests.
 */
class PurchaseOrderTestBase extends FunctionalTestBase {

  use PurchaseOrderTestTrait;

  /**
   * Storage for the faker data for purchase order.
   *
   * @var \Faker\Factory
   */
  protected $purchaseOrder;

  /**
   * Setup the basic variables.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->purchaseOrderFakerSetup();
  }

}
