<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Goods Receipt entities.
 */
class GoodsReceiptViewsData extends EntityViewsData {

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
