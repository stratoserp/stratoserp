<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display User timekeeping statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "user_timekeeping_statistics",
 *   label = @Translation("User timekeeping statistics"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class UserTimekeepingStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Timekeeping statistics');
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
      ->createInstance('user_timekeeping_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
