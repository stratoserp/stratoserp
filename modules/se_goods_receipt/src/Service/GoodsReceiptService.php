<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Service;

use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\se_item\Entity\Item;

/**
 * Provide simple services for goods receipts.
 */
class GoodsReceiptService implements GoodsReceiptServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function createItems(GoodsReceipt $goodsReceipt) {
    foreach ($goodsReceipt->se_item_lines as $index => $itemLine) {
      if (!empty($itemLine->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($itemLine->target_id)) {
          if ($item->se_serial->value !== $itemLine->serial) {
            $newItem = $item->createDuplicate();
            $newItem
              ->set('se_serial', $itemLine->serial)
              ->set('se_it_ref', $item->id())
              ->save();

            $goodsReceipt->se_item_lines[$index]->target_id = $newItem->id();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateFields(GoodsReceipt $goodsReceipt) {
    foreach ($goodsReceipt->se_item_lines as $itemLine) {
      /** @var \Drupal\se_item\Entity\Item $item */
      if ($item = Item::load($itemLine->target_id)) {
        if ($item->bundle() !== 'se_stock') {
          continue;
        }
        $item
          ->set('se_gr_ref', $goodsReceipt->id())
          ->set('se_po_ref', $goodsReceipt->se_po_ref->target_id)
          ->set('se_cost_price', $itemLine->price)
          ->save();
      }
    }
  }

}
