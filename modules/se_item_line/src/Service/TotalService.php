<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\se_item\Entity\Item;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Business service class for common custom related manipulations.
 */
class TotalService {

  /**
   * Calculate the total and more of an entity with item lines.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to update the totals for..
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The updated entity.
   */
  public function calculateTotal(EntityInterface $entity): EntityInterface {
    $total = 0;

    // If there isn't a total field, run away.
    if (!isset($entity->se_total)) {
      return $entity;
    }

    // Loop through the item lines to calculate total.
    foreach ($entity->se_item_lines as $index => $itemLine) {
      // If it's a timekeeping entry, and no price, load the associated item for price.
      if (isset($itemLine->target_id) && !isset($itemLine->price)) {
        if (($itemLine->target_type === 'se_timekeeping') && $timekeeping = Timekeeping::load($itemLine->target_id)) {
          $item = $timekeeping->se_it_ref->entity;
          $itemLine->price = $item->se_sell_price->value;
        }
      }

      // @todo This isn't really 'total' calculation, should be separated?
      if (empty($itemLine->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if (($item = Item::load($itemLine->target_id)) && $item->bundle() === 'se_stock') {
          $entity->se_item_lines[$index]->serial = $item->se_serial->value;
        }
      }

      $total += $itemLine->quantity * $itemLine->price;
    }

    $entity->set('se_total', $total);

    return $entity;
  }

}
