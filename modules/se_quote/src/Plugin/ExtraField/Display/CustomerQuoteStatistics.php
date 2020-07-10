<?php

declare(strict_types=1);

namespace Drupal\se_quote\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Customer quote statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "customer_quote_statistics",
 *   label = @Translation("Quote statistics"),
 *   bundles = {
 *     "node.se_customer",
 *   }
 * )
 */
class CustomerQuoteStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   *
   */
  public function getLabel() {
    return $this->t('Quote statistics');
  }

  /**
   *
   */
  public function getLabelDisplay() {
    return 'above';
  }

  /**
   *
   */
  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('customer_quote_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
