<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Service;

use Drupal\se_goods_receipt\Entity\GoodsReceipt;

/**
 * Interface for the goods receipts service.
 */
interface GoodsReceiptServiceInterface {

  /**
   * If there is a serial number, but its a new item, create as a new item.
   *
   * @param \Drupal\se_goods_receipt\Entity\GoodsReceipt $goodsReceipt
   *   The goods receipt to work with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createItems(GoodsReceipt $goodsReceipt);

  /**
   * Update the lines in a goods receipt with values from the items.
   *
   * @param \Drupal\se_goods_receipt\Entity\GoodsReceipt $goodsReceipt
   *   The goods receipt to work with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateFields(GoodsReceipt $goodsReceipt);

}
