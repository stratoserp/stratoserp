<?php

declare(strict_types=1);

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
 *     "se_customer.se_customer",
 *   }
 * )
 */
class CustomerTicketStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * Provide the label for the field.
   */
  public function getLabel() {
    return $this->t('Customer ticket statistics');
  }

  /**
   * Return the default display for the label.
   */
  public function getLabelDisplay() {
    return 'above';
  }

  /**
   * Show the actual statistics.
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
