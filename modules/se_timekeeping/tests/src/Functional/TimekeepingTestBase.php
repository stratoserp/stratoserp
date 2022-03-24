<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_ticket\Traits\TicketTestTrait;
use Drupal\Tests\se_timekeeping\Traits\TimekeepingTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Timekeeping entity.
 */
class TimekeepingTestBase extends FunctionalTestBase {

  use CustomerTestTrait;
  use TicketTestTrait;
  use TimekeepingTestTrait;

  /**
   * Storage for the faker data for timekeeping.
   *
   * @var \Faker\Factory
   */
  protected $timekeeping;

  /**
   * Basic setup.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->timekeepingFakerSetup();
  }

}
