<?php

namespace Drupal\se_quote\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExtraFieldDisplay(
 *   id = "quote_statistics_user",
 *   label = @Translation("Quote statistics per user"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class QuoteStatisticsUser extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('User invoice statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('quote_statistics_user', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}