<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic customer tests.
 */
class CustomerTestBase extends FunctionalTestBase {

  use CustomerTestTrait;

  /**
   * Setup the basic customer tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->customerFakerSetup();
  }

}
