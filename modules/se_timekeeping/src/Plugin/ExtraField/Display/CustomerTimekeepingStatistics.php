<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Customer timekeeping statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "customer_timekeeping_statistics",
 *   label = @Translation("Customer timekeeping statistics"),
 *   bundles = {
 *     "se_customer.se_customer",
 *   }
 * )
 */
class CustomerTimekeepingStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Customer timekeeping statistics');
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
      ->createInstance('customer_timekeeping_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => \Drupal::service('renderer')->render($block)],
    ];
  }

}
