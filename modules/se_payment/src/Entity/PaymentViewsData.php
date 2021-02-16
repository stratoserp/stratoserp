<?php

declare(strict_types=1);

namespace Drupal\se_payment\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Payment entities.
 */
class PaymentViewsData extends EntityViewsData {

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
