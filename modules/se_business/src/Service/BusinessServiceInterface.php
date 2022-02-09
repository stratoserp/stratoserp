<?php

declare(strict_types=1);

namespace Drupal\se_business\Service;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

interface BusinessServiceInterface {

  /**
   * Given any entity with a business, return the (first) business.
   *
   * @param \Drupal\stratoserp\Entity\StratosEntityBaseInterface $entity
   *   Entity to return the business for.
   *
   * @return bool|\Drupal\stratoserp\Entity\StratosEntityBaseInterface
   *   Business entity.
   */
  public function lookupBusiness(StratosEntityBaseInterface $entity);

  /**
   * Create a timestamp from current year, month and the business invoice day.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business to make the timestamp from.
   *
   * @return int
   *   The timestamp.
   */
  public function getInvoiceDayTimestamp($business): int;

}
