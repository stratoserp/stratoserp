<?php

declare(strict_types=1);

namespace Drupal\Tests\se_store\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;

/**
 * Test Store Service.
 *
 * @covers \Drupal\se_store
 * @group se_store
 * @group stratoserp
 */
class StoreServiceTest extends StoreTestBase {

  use ItemTestTrait;
  use InvoiceTestTrait;

  /**
   * Test store lookup service.
   *
   * Ensure that the store lookup service will return
   * the customer entity if called with a customer entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStoreServiceWithCustomer(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $store = $this->addMainStore($customer);

    $customerStores = \Drupal::service('se_store.service')->loadMainStoresByCustomer($customer);
    $loadedStore = reset($customerStores);

    self::assertSame(
      $loadedStore,
      $store->id()
    );

    $this->drupalLogout();
  }

  /**
   * Test customer lookup service with an invoice.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testStoreServiceWithInvoice(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $customer = $this->addCustomer();
    $store = $this->addMainStore($customer);

    $items = $this->createItems();
    $invoice = $this->addInvoice($customer, $items);

    $invoiceStores = \Drupal::service('se_store.service')->loadMainStoresByCustomer($invoice);
    $invoiceStore = reset($invoiceStores);

    self::assertSame(
      $invoiceStore,
      $store->id()
    );

    $this->drupalLogout();
  }

}
