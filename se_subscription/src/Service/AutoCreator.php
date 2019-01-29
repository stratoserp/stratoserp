<?php

namespace Drupal\se_subscription\Service;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\se_subscription\Entity\Subscription;

class AutoCreator {

  public function create(Node $node) {
    $paragraphs = $node->field_in_items->getValue();
    if (empty($paragraphs) || $node->subscription_created) {
      return FALSE;
    }

    foreach ($paragraphs as $paragraph) {
      $line = Paragraph::load($paragraph['target_id']);
      $item_ref = $line->field_it_stock_item->target_id;
      $item = Node::load($item_ref);
      if (empty($item->field_it_subscription_period->duration)) {
        continue;
      }

      $subscription = Subscription::create([
        'user_id' => get_current_user(),
        'name' => $this->subscriptionName($item),
        'period' => $item->field_it_subscription_period->duration,
        'last_billed' => time(),
        'field_su_invoice_ref' => $node->id(),
      ]);
      $subscription->save();
    }
  }

  /**
   * Given an item, create a name for the subscription.
   * @param $item
   *
   * @return mixed
   */
  private function subscriptionName($item) {
    // TODO - What should this be.. customisable?
    return $item->title;
  }

}
