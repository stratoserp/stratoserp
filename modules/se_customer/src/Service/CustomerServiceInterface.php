<?php

declare(strict_types=1);

namespace Drupal\se_customer\Service;

use Drupal\se_customer\Entity\Customer;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Interface for the customer service.
 */
interface CustomerServiceInterface {

  /**
   * Given any entity with a customer, return the (first) customer.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   Entity to return the customer for.
   *
   * @return bool|\Drupal\stratoserp\Entity\StratosEntityBaseInterface
   *   Customer entity.
   */
  public function lookupCustomer(StratosEntityBaseInterface $entity);

  /**
   * Create a timestamp from current year, month and the customer invoice day.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to make the timestamp from.
   *
   * @return int
   *   The timestamp.
   */
  public function getInvoiceDayTimestamp(Customer $customer): int;

}
