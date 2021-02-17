<?php

namespace Drupal\Tests\se_quote\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Quote create/update/delete tests.
 *
 * @coversDefault Drupal\se_quote
 * @group se_quote
 * @group stratoserp
 */
class QuoteCrudTest extends FunctionalTestBase {

  use ItemTestTrait;

  /**
   * Quote holding var.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $quote;

  /**
   * Faker Factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

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
