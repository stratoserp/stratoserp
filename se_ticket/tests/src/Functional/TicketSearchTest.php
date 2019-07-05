<?php

namespace Drupal\Tests\se_ticket\ExistingSite;


use Drupal\Tests\se_testing\Functional\TicketTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_ticket
 * @group stratoserp
 *
 */
class TicketSearchTest extends TicketTestBase {
  protected $staff;

  public function testTicketSearch() {

    $web_assert = $this->assertSession();

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->drupalGet('se/tickets');
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();

    $page->fillField('description', 'scam');

    $submit_button = $page->findButton('Apply');
    $submit_button->press();
    $web_assert->statusCodeEquals(200);

    $this->drupalLogout();

  }

}