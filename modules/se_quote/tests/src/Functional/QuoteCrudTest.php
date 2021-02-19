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
    $this->drupalLogin($this->staff);
    $testBusiness = $this->addBusiness();
    $items = $this->createItems();
    $this->drupalLogout();

    $this->drupalLogin($this->customer);
    $this->addQuote($testBusiness, $items, FALSE);
    $this->drupalLogout();

    $this->drupalLogin($this->staff);
    $this->addQuote($testBusiness, $items);
    $this->drupalLogout();

    $this->drupalLogin($this->owner);
    $this->addQuote($testBusiness, $items);
    $this->drupalLogout();
  }

}
