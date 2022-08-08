<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_quote\Traits\QuoteTestTrait;

/**
 * Test Customer Service.
 *
 * @covers \Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerServiceTest extends CustomerTestBase {

  use ItemTestTrait;
  use InvoiceTestTrait;
  use QuoteTestTrait;

  /**
   * Test customer lookup service.
   *
   * Ensure that the customer lookup service will return
   * the customer if called with a customer entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCustomerServiceWithCustomer(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->customerFakerSetup();
    $customer = $this->addCustomer();

    self::assertSame(
      \Drupal::service('se_customer.service')->lookupCustomer($customer),
      $customer
    );

    $this->drupalLogout();
  }

  /**
   * Test customer lookup service with an invoice.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCustomerServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->customerFakerSetup();
    $customer = $this->addCustomer();

    $items = $this->createItems();

    $invoice = $this->addInvoice($customer, $items);

    $invoiceCustomer = \Drupal::service('se_customer.service')->lookupCustomer($invoice);
    self::assertSame(
      $invoiceCustomer->id(),
      $customer->id()
    );

    $this->drupalLogout();
  }

  /**
   * Test customer lookup service with a quote.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCustomerServiceWithQuote(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->customerFakerSetup();
    $customer = $this->addCustomer();

    $items = $this->createItems();

    $quote = $this->addQuote($customer, $items);

    $quoteCustomer = \Drupal::service('se_customer.service')->lookupCustomer($quote);
    self::assertSame(
      $quoteCustomer->id(),
      $customer->id()
    );

    $this->drupalLogout();
  }

}
