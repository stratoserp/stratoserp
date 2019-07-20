<?php

namespace Drupal\se_purchase_order\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Customer purchase order statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "customer_purchase_order_statistics",
 *   label = @Translation("Customer purchase order statistics"),
 *   bundles = {
 *     "node.se_customer",
 *   }
 * )
 */
class CustomerPurchaseOrderStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('Purchase order statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('customer_purchase_order_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}