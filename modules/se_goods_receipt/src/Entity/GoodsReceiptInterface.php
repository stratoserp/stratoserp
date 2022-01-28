<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Provides an interface for defining Goods Receipt entities.
 *
 * @ingroup se_goods_receipt
 */
interface GoodsReceiptInterface extends StratosLinesEntityBaseInterface {

  /**
   * Return the total amount of the entity.
   */
  public function getTotal(): int;

}
