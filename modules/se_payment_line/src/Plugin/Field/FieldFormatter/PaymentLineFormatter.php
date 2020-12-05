<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'dynamic entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "se_payment_line_formatter",
 *   label = @Translation("Payment line formatter"),
 *   description = @Translation("Payment line formatter"),
 *   field_types = {
 *     "se_payment_line"
 *   }
 * )
 */
class PaymentLineFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * Re-implementation of viewElements from EntityReferenceLabelFormatter.
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $row = [];
    $rows = [];

    $headers = [
      t('Invoice No.'),
      t('Invoice'),
      t('Amount'),
      t('Payment date'),
    ];

    /**
     * @var \Drupal\node\Entity\Node $entity
     */
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $row = [];

      $uri = $entity->toUrl();

      $date = new DrupalDateTime($items[$delta]->completed_date, DateTimeItemInterface::STORAGE_TIMEZONE);
      $displayDate = $date->getTimestamp() !== 0 ? gmdate('Y-m-d', $date->getTimestamp()) : '';

      $row = [
        Link::fromTextAndUrl($entity->se_in_id->value, $uri),
        Link::fromTextAndUrl($entity->title->value, $uri),
        \Drupal::service('se_accounting.currency_format')->formatDisplay((int) ($items[$delta]->amount ?? 0)),
        $displayDate,
      ];

      $rows[] = $row;
    }

    // Now wrap the lines into a bundle.
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $headers,
    ];
  }

}
