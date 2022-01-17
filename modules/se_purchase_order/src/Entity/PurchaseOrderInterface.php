<?php

namespace Drupal\se_purchase_order\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining PurchaseOrder entities.
 *
 * @ingroup se_purchase_order
 */
interface PurchaseOrderInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Return the total amount of the entity.
   */
  public function getTotal(): int;

}
