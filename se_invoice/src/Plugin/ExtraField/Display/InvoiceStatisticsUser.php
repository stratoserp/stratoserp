<?php

namespace Drupal\se_invoice\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExtraFieldDisplay(
 *   id = "invoice_statistics_user",
 *   label = @Translation("Invoice statistics per user"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class InvoiceStatisticsUser extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('User invoice statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('invoice_statistics_user', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}