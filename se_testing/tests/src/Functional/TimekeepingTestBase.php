<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\TicketTestTrait;
use Drupal\Tests\se_testing\Traits\TimekeepingTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

class TimekeepingTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use CustomerTestTrait;
  use UserCreateTrait;
  use TicketTestTrait;
  use TimekeepingTestTrait;

  protected $customer;
  protected $ticket;
  protected $timekeeping;

  protected function setUp() {
    parent::setUp();
    $this->customerFakerSetup();
    $this->ticketFakerSetup();
    $this->timekeepingFakerSetup();
  }

}
