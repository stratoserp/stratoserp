<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_ticket\Traits\TicketTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Ticket entity.
 */
class TicketTestBase extends FunctionalTestBase {

  use TicketTestTrait;
  use CustomerTestTrait;

  /**
   * Storage for the faker data for ticket.
   *
   * @var \Faker\Factory
   */
  protected $ticket;

  /**
   * Basic setup.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->ticketFakerSetup();
  }

}
