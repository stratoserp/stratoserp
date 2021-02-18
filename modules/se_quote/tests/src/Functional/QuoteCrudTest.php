<?php

namespace Drupal\Tests\se_quote\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Quote create/update/delete tests.
 *
 * @coversDefault Drupal\se_quote
 * @group se_quote
 * @group stratoserp
 */
class QuoteCrudTest extends QuoteTestBase {

  use ItemTestTrait;
  use BusinessTestTrait;

  /**
   * Test adding an quote.
   */
  public function testQuoteAdd() {
    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();

    $items = $this->createItems();
    $this->addQuote($testBusiness, $items);

    $this->drupalLogout();
  }

}
