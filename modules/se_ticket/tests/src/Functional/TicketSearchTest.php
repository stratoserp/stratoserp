<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Functional;

/**
 * Class for testing ticket searching.
 *
 * @covers \Drupal\se_ticket
 * @uses \Drupal\se_customer\Entity\Customer
 * @group se_ticket
 * @group stratoserp
 */
class TicketSearchTest extends TicketTestBase {

  /**
   * Test searching for tickets.
   */
  public function testTicketSearch(): void {
    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $testCustomer = $this->addCustomer();

    $tickets = [];
    $count = 15;
    for ($i = 0; $i <= $count; $i++) {
      $this->ticketFakerSetup();
      $tickets[] = $this->addTicket($testCustomer);
    }

    $this->drupalGet('se/tickets');
    $this->assertSession()->statusCodeEquals(200);

    $searchTicket = random_int(0, $count);
    $searchWord = implode(' ', array_slice(explode(' ', $tickets[$searchTicket]->name->value), 0, 2));
    unset($tickets[$searchTicket]);

    $page = $this->getCurrentPage();
    $page->fillField('edit-name', $searchWord);
    $submitButton = $page->findButton('Apply');
    $submitButton->press();
    $this->assertSession()->statusCodeEquals(200);

    sleep(1);

    $content = $this->getTextContent();
    self::assertStringContainsString($searchWord, $content);
    foreach ($tickets as $ticket) {
      $searchWord = implode(' ', array_slice(explode(' ', $ticket->name->value), 0, 2));
      self::assertStringNotContainsString($searchWord, $content);
    }

    $this->drupalLogout();
  }

}
