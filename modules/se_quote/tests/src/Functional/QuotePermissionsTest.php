<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test quote permissions.
 *
 * @coversDefault Drupal\se_invoice
 * @group se_quote
 * @group stratoserp
 */
class QuotePermissionsTest extends FunctionalTestBase {

  /**
   * Faker factory for customer.
   *
   * @var \Faker\Factory
   */
  protected $customer;

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private $pages = [
    '/quote/add',
    '/se/customers/quote-list',
  ];

  /**
   * Run the basic permissions check on the pages.
   */
  public function testQuotePermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
