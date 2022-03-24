<?php

declare(strict_types=1);

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Invoice entity.
 */
class InvoiceTestBase extends FunctionalTestBase {

  use InvoiceTestTrait;

  /**
   * Storage for the faker data for invoice.
   *
   * @var \Faker\Factory
   */
  protected $invoice;

  /**
   * Setup the basic customer tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->invoiceFakerSetup();
  }

}
