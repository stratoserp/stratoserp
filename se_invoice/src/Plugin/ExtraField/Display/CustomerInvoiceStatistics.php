<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display customer invoice statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "customer_invoice_statistics",
 *   label = @Translation("Customer invoice statistics"),
 *   bundles = {
 *     "node.se_customer",
 *   }
 * )
 */
class CustomerInvoiceStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   *
   */
  public function getLabel() {
    return $this->t('Customer invoice statistics');
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
      ->createInstance('customer_invoice_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
