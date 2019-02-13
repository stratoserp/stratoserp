<?php

namespace Drupal\se_information\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Information entities.
 */
class InformationViewsData extends EntityViewsData {

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
