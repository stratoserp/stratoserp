<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Service;

use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\se_item\Entity\Item;
use Drupal\stratoserp\Constants;

class GoodsReceiptService {
 
  public function createItems(GoodsReceipt $goodsReceipt) {
    foreach ($entity->se_item_lines as $index => $itemLine) {
      if (!empty($itemLine->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($itemLine->target_id)) {
          if ($item->se_serial->value !== $itemLine->serial) {
            $newItem = $item->createDuplicate();
            $newItem
              ->set('se_serial', $itemLine->serial)
              ->set('se_it_ref', $item->id())
              ->save();

            $entity->se_item_lines[$index]->target_id = $newItem->id();
          }
        }
      }
    }
  }
  
  public function updateFields(GoodsReceipt $goodsReceipt) {
    foreach ($entity->se_item_lines as $itemLine) {
      /** @var \Drupal\se_item\Entity\Item $item */
      if ($item = Item::load($itemLine->target_id)) {
        if ($item->bundle() !== 'se_stock') {
          continue;
        }
        $item
          ->set('se_gr_ref', $entity->id())
          ->set('se_po_ref', $entity->se_po_ref->target_id)
          ->set('se_cost_price', $itemLine->price)
          ->save();
      }
    }
  }
  
}