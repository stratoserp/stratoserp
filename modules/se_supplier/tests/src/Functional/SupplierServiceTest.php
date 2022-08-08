<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

use Drupal\Tests\se_invoice\Traits\InvoiceTestTrait;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_quote\Traits\QuoteTestTrait;

/**
 * Test Supplier Service.
 *
 * @covers \Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierServiceTest extends SupplierTestBase {

  use ItemTestTrait;
  use InvoiceTestTrait;
  use QuoteTestTrait;

  /**
   * Test supplier lookup service.
   *
   * Ensure that the supplier lookup service will return
   * the supplier if called with a supplier entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSupplierServiceWithSupplier(): void {

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);

    $this->supplierFakerSetup();
    $supplier = $this->addSupplier();

    self::assertSame(
      \Drupal::service('se_supplier.service')->lookupSupplier($supplier),
      $supplier
    );

    $this->drupalLogout();
  }

}
