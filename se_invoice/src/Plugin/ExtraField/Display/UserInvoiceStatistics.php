<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display User invoice statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "user_invoice_statistics",
 *   label = @Translation("User invoice statistics"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class UserInvoiceStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   *
   */
  public function getLabel() {
    return $this->t('User invoice statistics');
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
      ->createInstance('user_invoice_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
