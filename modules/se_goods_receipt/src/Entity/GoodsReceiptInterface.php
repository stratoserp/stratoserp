<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Goods Receipt entities.
 *
 * @ingroup se_goods_receipt
 */
interface GoodsReceiptInterface extends StratosEntityBaseInterface {

  /**
   * Return the search prefix for this entity type.
   */
  public function getSearchPrefix(): string;

  /**
   * Return the total amount of the entity.
   */
  public function getTotal(): int;

}
