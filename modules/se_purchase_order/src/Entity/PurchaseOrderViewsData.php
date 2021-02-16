<?php

namespace Drupal\se_purchase_order\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for PurchaseOrder entities.
 */
class PurchaseOrderViewsData extends EntityViewsData {

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
