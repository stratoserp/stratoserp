<?php

declare(strict_types=1);

namespace Drupal\Tests\se_store\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_store\Traits\StoreTestTrait;
use Drupal\Tests\se_supplier\Traits\SupplierTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic store tests.
 */
class StoreTestBase extends FunctionalTestBase {

  use StoreTestTrait;
  use CustomerTestTrait;
  use SupplierTestTrait;

  /**
   * Faker factory for store.
   *
   * @var \Faker\Factory
   */
  protected $store;

  /**
   * Setup the basic store tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->storeFakerSetup();
  }

}
