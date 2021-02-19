<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic business tests.
 */
class BusinessTestBase extends FunctionalTestBase {

  use BusinessTestTrait;

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * Setup the basic business tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->businessFakerSetup();
  }

}
