<?php

namespace Drupal\se_ticket\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExtraFieldDisplay(
 *   id = "ticket_statistics_user",
 *   label = @Translation("Ticket statistics per user"),
 *   bundles = {
 *     "user.*",
 *   }
 * )
 */
class TicketStatisticsUser extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  public function getLabel() {
    return $this->t('Ticket statistics');
  }

  public function getLabelDisplay() {
    return 'above';
  }

  public function viewElements(ContentEntityInterface $entity) {
    if (!$block = \Drupal::service('plugin.manager.block')
      ->createInstance('ticket_statistics_user', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => render($block)]
    ];
  }

}