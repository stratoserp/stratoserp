<?php

namespace Drupal\se_purchase_order\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Provides an interface for defining PurchaseOrder entities.
 *
 * @ingroup se_purchase_order
 */
interface PurchaseOrderInterface extends StratosLinesEntityBaseInterface {

  /**
   * Return the total amount of the entity.
   */
  public function getTotal(): int;

}
