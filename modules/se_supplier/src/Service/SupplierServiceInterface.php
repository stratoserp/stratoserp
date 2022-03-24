<?php

declare(strict_types=1);

namespace Drupal\se_supplier\Service;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

interface SupplierServiceInterface {

  /**
   * Given any entity with a supplier, return the (first) supplier.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   Entity to return the supplier for.
   *
   * @return bool|\Drupal\stratoserp\Entity\StratosEntityBaseInterface
   *   Supplier entity.
   */
  public function lookupSupplier(StratosEntityBaseInterface $entity);

  /**
   * Create a timestamp from current year, month and the supplier invoice day.
   *
   * @param \Drupal\se_supplier\Entity\Supplier $supplier
   *   The supplier to make the timestamp from.
   *
   * @return int
   *   The timestamp.
   */
  public function getInvoiceDayTimestamp($supplier): int;

}
