<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Class for testing ticket searching.
 *
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketSearchTest extends FunctionalTestBase {

  /**
   * Storage for the faker data for the user.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * Test searching for tickets.
   */
  public function testTicketSearch(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $test_customer = $this->addCustomer();

    $tickets = [];
    $count = 15;
    for ($i = 0; $i <= $count; $i++) {
      $this->ticketFakerSetup();
      $tickets[] = $this->addTicket($test_customer);
    }

    $this->drupalGet('se/ticket-list');
    $this->assertSession()->statusCodeEquals(200);

    $search_ticket = random_int(0, $count);
    $search_word = implode(' ', array_slice(explode(' ', $tickets[$search_ticket]->title->value), 0, 2));
    unset($tickets[$search_ticket]);

    $page = $this->getCurrentPage();
    $page->fillField('edit-description', $search_word);
    $submit_button = $page->findButton('Apply');
    $submit_button->press();
    $this->assertSession()->statusCodeEquals(200);

    sleep(1);

    $content = $this->getTextContent();
    $this->assertStringContainsString($search_word, $content);
    foreach ($tickets as $ticket) {
      $search_word = implode(' ', array_slice(explode(' ', $ticket->title->value), 0, 2));
      $this->assertStringNotContainsString($search_word, $content);
    }

    $this->drupalLogout();

  }

}
