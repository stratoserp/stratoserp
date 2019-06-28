<?php

namespace Drupal\se_recurring\Service;

use Drupal\node\Entity\Node;

class AutoCreator {

  public function create(Node $node) {

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
