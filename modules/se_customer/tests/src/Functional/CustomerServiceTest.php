<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Customer Service.
 */
class CustomerServiceTest extends FunctionalTestBase {

  use ItemTestTrait;

  /**
   * Test customer lookup service.
   *
   * Ensure that the customer lookup service will return
   * the customer node if called with a customer node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCustomerServiceWithCustomer(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();

    self::assertSame(
      \Drupal::service('se_customer.service')->lookupCustomer($customer),
      $customer
    );

    $this->drupalLogout();
  }

  /**
   * Test customer lookup service with an invoice node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testCustomerServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

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
   * Test customer lookup service with an invoice node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testCustomerServiceWithQuote(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

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
