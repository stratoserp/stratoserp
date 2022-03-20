<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Business purchase order statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "business_purchase_order_statistics",
 *   label = @Translation("Business purchase order statistics"),
 *   bundles = {
 *     "se_business.se_business",
 *   }
 * )
 */
class BusinessPurchaseOrderStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * Provide the label for the field.
   */
  public function getLabel() {
    return $this->t('Purchase order statistics');
  }

  /**
   * Return the default display for the label.
   */
  public function getLabelDisplay() {
    return 'above';
  }

  /**
   * Show the actual statistics.
   */
  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('business_purchase_order_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => \Drupal::service('renderer')->render($block)],
    ];
  }

}
