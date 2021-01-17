<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Service;

use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;
use Drupal\se_item\Entity\Item;
use Drupal\stratoserp\ErpCore;

/**
 * Customer service class for common custom related manipulations.
 */
class TotalService {

  /**
   * Calculate the total and more of a node with item lines.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Node to update the totals for..
   *
   * @return \Drupal\node\Entity\Node
   *   The updated node.
   */
  public function calculateTotal(Node $node): Node {
    $total = 0;
    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$node->bundle()];

    // Loop through the item lines to calculate total.
    foreach ($node->{$bundleFieldType . '_lines'} as $index => $itemLine) {
      // If its a comment type, load the comment item to get the price.
      if (($itemLine->target_type === 'comment') && $comment = Comment::load($itemLine->target_id)) {
        $item = $comment->se_tk_item->entity;
        $itemLine->price = $item->se_it_sell_price->value;
      }

      // This isn't really 'total' calculation, should be separated?
      if (empty($itemLine->serial)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if (($item = Item::load($itemLine->target_id)) && $item->bundle() === 'se_stock') {
          $node->{$bundleFieldType . '_lines'}[$index]->serial = $item->se_it_serial->value;
        }
      }

      $total += $itemLine->quantity * $itemLine->price;
    }

    $node->set($bundleFieldType . '_total', $total);

    return $node;
  }

}
