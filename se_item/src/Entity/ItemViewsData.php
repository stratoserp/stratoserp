<?php

namespace Drupal\se_item\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Item entities.
 */
class ItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
