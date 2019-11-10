<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\ContactTestTrait;
use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\TicketTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * Base functions for testing tickets.
 */
class TicketTestBase extends FunctionalTestBase {

  // Now our own Traits.
  use ContactTestTrait;
  use CustomerTestTrait;
  use UserCreateTrait;
  use TicketTestTrait;

  protected $ticket;
  protected $customer;

  /**
   * Setup basic information.
   */
  protected function setUp() {
    parent::setUp();
    $this->customerFakerSetup();
    $this->contactFakerSetup();
    $this->ticketFakerSetup();
  }

}
