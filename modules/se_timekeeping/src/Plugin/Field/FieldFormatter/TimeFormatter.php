<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\se_timekeeping\Service\TimeFormatInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of a time formatter.
 *
 * @FieldFormatter(
 *   id = "TimeFormater",
 *   label = @Translation("Time formatter"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class TimeFormatter extends FormatterBase {

  /** @var \Drupal\se_timekeeping\Service\TimeFormatInterface */
  protected TimeFormatInterface $timeFormat;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, TimeFormatInterface $timeFormat) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->timeFormat = $timeFormat;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('se_timekeeping.time_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = (string) $this->t('Displays time fields using hours and minutes.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#markup' => $this->timeFormat->formatHours($item->value),
      ];
    }

    return $element;
  }

}
