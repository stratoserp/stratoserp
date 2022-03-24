<?php

declare(strict_types=1);

namespace Drupal\Tests\se_supplier\Functional;

/**
 * Test getting, setting and adjusting supplier outstanding balance.
 *
 * @coversDefault Drupal\se_supplier
 * @group se_supplier
 * @group stratoserp
 */
class SupplierBalanceTest extends SupplierTestBase {

  /**
   * Test getting, setting and adjusting supplier balance.
   */
  public function testSupplierBalance(): void {

    $this->drupalLogin($this->staff);
    $supplier = $this->addSupplier();

    $supplier->setBalance(25017);
    self::assertEquals(25017, $supplier->getBalance());

    $supplier->adjustBalance(-5012);
    self::assertEquals(20005, $supplier->getBalance());

    $supplier->adjustBalance(-20005);
    self::assertEquals(0, $supplier->getBalance());

    $this->drupalLogout();
  }

}
