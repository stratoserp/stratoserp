<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Test Business Service.
 */
class BusinessServiceTest extends BusinessTestBase {

  use ItemTestTrait;
  use InvoiceTestTrait;

  /**
   * Test business lookup service.
   *
   * Ensure that the business lookup service will return
   * the business if called with a business entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBusinessServiceWithBusiness(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();

    self::assertSame(
      \Drupal::service('se_business.service')->lookupBusiness($business),
      $business
    );

    $this->drupalLogout();
  }

  /**
   * Test business lookup service with an invoice.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException|\Behat\Mink\Exception\ExpectationException
   */
  public function testBusinessServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();

    $items = $this->createItems();
    $this->invoiceFakerSetup();

    $invoice = $this->addInvoice($business, $items);

    $invoiceBusiness = \Drupal::service('se_business.service')->lookupBusiness($invoice);
    self::assertSame(
      $invoiceBusiness->id(),
      $business->id()
    );

    $this->drupalLogout();
  }

  /**
   * Test business lookup service with an quote.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testBusinessServiceWithQuote(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $business = $this->addBusiness();

    $items = $this->createItems();
    $quote = $this->addQuote($business, $items);

    $quoteBusiness = \Drupal::service('se_business.service')->lookupBusiness($quote);
    self::assertSame(
      $quoteBusiness->id(),
      $business->id()
    );

    $this->drupalLogout();
  }

}
