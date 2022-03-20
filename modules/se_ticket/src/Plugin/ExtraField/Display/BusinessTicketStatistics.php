<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field to display Business ticket statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "business_ticket_statistics",
 *   label = @Translation("Business ticket statistics"),
 *   bundles = {
 *     "se_business.se_business",
 *   }
 * )
 */
class BusinessTicketStatistics extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * Provide the label for the field.
   */
  public function getLabel() {
    return $this->t('Business ticket statistics');
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
      ->createInstance('business_ticket_statistics', [])
      ->build()) {
      return [];
    }

    return [
      ['#markup' => \Drupal::service('renderer')->render($block)],
    ];
  }

}
