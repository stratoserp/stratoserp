<?php

namespace Drupal\se_ticket\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Customer ticket statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "customer_ticket_statistics",
 *   label = @Translation("Customer ticket statistics"),
 *   bundles = {
 *     "node.se_customer",
 *   }
 * )
 */
class CustomerTicketStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   *
   */
  public function getLabel() {
    return $this->t('Customer ticket statistics');
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
      ->createInstance('customer_ticket_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)],
    ];
  }

}
