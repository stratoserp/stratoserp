<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\ExistingSite;

use Drupal\Tests\se_testing\Functional\TicketTestBase;

/**
 * @coversDefault Drupal\se_ticket
 * @group se_ticket
 * @group stratoserp
 */
class TicketSearchTest extends TicketTestBase {
  protected $staff;

  /**
   *
   */
  public function testTicketSearch() {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $test_customer = $this->addCustomer();

    $tickets = [];
    $count = 15;
    for ($i = 0; $i < $count; $i++) {
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

    $content = $this->getTextContent();
    $this->assertContains($search_word, $content);
    foreach ($tickets as $ticket) {
      $search_word = implode(' ', array_slice(explode(' ', $ticket->title->value), 0, 2));
      $this->assertNotContains($search_word, $content);
    }

    $this->drupalLogout();

  }

}
