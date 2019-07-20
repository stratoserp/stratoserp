<?php

namespace Drupal\se_quote\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display User purchase order statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "user_purchase_order_statistics",
 *   label = @Translation("User purchase order statistics"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class UserPurchaseOrderStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('User purchase order statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('user_purchase_order_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}