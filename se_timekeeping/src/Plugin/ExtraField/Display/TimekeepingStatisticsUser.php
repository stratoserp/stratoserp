<?php

namespace Drupal\se_timekeeping\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExtraFieldDisplay(
 *   id = "timekeeping_statistics_user",
 *   label = @Translation("Timekeeping statistics per user"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class TimekeepingStatisticsUser extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('Timekeeping statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('timekeeping_statistics_user', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}