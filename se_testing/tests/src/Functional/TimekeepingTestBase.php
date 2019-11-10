<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\TicketTestTrait;
use Drupal\Tests\se_testing\Traits\TimekeepingTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * Base functions for testing timekeeping.
 */
class TimekeepingTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use CustomerTestTrait;
  use UserCreateTrait;
  use TicketTestTrait;
  use TimekeepingTestTrait;

  protected $customer;
  protected $ticket;
  protected $timekeeping;

  /**
   * Setup basic information.
   */
  protected function setUp() {
    parent::setUp();
    $this->customerFakerSetup();
    $this->ticketFakerSetup();
    $this->timekeepingFakerSetup();
  }

}
