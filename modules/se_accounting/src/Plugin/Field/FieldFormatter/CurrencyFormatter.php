<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\se_accounting\Service\CurrencyFormatServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of StratosERP accounting formatter.
 *
 * @FieldFormatter(
 *   id = "se_currency_formatter",
 *   label = @Translation("Currency formatter"),
 *   field_types = {
 *     "se_currency"
 *   }
 * )
 */
class CurrencyFormatter extends FormatterBase {

  /**
   * @var \Drupal\se_accounting\Service\CurrencyFormatServiceInterface*/
  protected CurrencyFormatServiceInterface $currencyFormat;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, CurrencyFormatServiceInterface $currencyFormat) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currencyFormat = $currencyFormat;
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
      $container->get('se_accounting.currency_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays currency fields using format selected in settings.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = [
        '#markup' => $this->currencyFormat->formatDisplay((int) $item->value),
      ];
    }

    return $element;
  }

}
