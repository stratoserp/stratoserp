<?php

declare(strict_types=1);

namespace Drupal\Tests\se_quote\Functional;

/**
 * Test quote permissions.
 *
 * @covers \Drupal\se_invoice
 * @uses \Drupal\se_customer\Entity\Customer
 * @group se_quote
 * @group stratoserp
 */
class QuotePermissionsTest extends QuoteTestBase {

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private array $pages = [
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
