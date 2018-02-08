<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the Invoice pages is reachable.
 *
 * @group se_invoice
 */
class InvoiceTest extends BrowserTestBase {

  protected static $modules = ['redis', 'node', 'se_invoice'];

  public function setUp() {
    parent::setUp();
  }

  public function testTotalCalculation() {
    $account = $this->drupalCreateUser(['administer drupal']);
    $this->drupalLogin($account);

    $this->drupalGet('node/add/se_invoice');
    $this->assertSession()->statusCodeEquals(200);

  }
}