<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_quote\Traits\QuoteTestTrait;

/**
 * Test Business Service.
 *
 * @coversDefault Drupal\se_business
 * @group se_business
 * @group stratoserp
 */
class BusinessServiceTest extends BusinessTestBase {

  use ItemTestTrait;
  use InvoiceTestTrait;
  use QuoteTestTrait;

  /**
   * Test business lookup service.
   *
   * Ensure that the business lookup service will return
   * the business if called with a business entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBusinessServiceWithBusiness(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->businessFakerSetup();
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
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBusinessServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->businessFakerSetup();
    $business = $this->addBusiness();

    $items = $this->createItems();

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
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBusinessServiceWithQuote(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->businessFakerSetup();
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
