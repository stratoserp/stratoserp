<?php

namespace Drupal\se_ticket\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExtraFieldDisplay(
 *   id = "ticket_statistics_customer",
 *   label = @Translation("Ticket statistics per customer"),
 *   bundles = {
 *     "node.*",
 *   }
 * )
 */
class TicketStatisticsCustomer extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('Ticket statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('ticket_statistics_customer', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}