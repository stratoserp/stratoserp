<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_supplier\Traits\SupplierTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic supplier tests.
 */
class SupplierTestBase extends FunctionalTestBase {

  use SupplierTestTrait;

  /**
   * Setup the basic supplier tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->supplierFakerSetup();
  }

}
