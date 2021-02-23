<?php

declare(strict_types=1);

namespace Drupal\se_quote\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Business quote statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "business_quote_statistics",
 *   label = @Translation("Quote statistics"),
 *   bundles = {
 *     "se_business",
 *   }
 * )
 */
class BusinessQuoteStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * Provide the label for the field.
   */
  public function getLabel() {
    return $this->t('Quote statistics');
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
      ->createInstance('business_quote_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
