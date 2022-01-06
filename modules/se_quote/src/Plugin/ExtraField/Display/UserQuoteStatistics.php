<?php

declare(strict_types=1);

namespace Drupal\se_quote\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display User quote statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "user_quote_statistics",
 *   label = @Translation("Quote statistics per user"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class UserQuoteStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('User quote statistics');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'above';
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('user_quote_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
