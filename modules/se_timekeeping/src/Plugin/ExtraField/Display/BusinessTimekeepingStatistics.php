<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Business timekeeping statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "business_timekeeping_statistics",
 *   label = @Translation("Business timekeeping statistics"),
 *   bundles = {
 *     "se_business.se_business",
 *   }
 * )
 */
class BusinessTimekeepingStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Business timekeeping statistics');
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
      ->createInstance('business_timekeeping_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => \Drupal::service('renderer')->render($block)],
    ];
  }

}
