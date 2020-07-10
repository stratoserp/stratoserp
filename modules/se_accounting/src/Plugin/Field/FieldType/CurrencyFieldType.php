<?php

declare(strict_types=1);

namespace Drupal\se_accounting\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\NumericItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'se_currency' field type.
 *
 * @FieldType(
 *   id = "se_currency",
 *   label = @Translation("Currency"),
 *   description = @Translation("An extension to integer type specifically for currency."),
 *   category = @Translation("StratosERP"),
 *   default_widget = "se_currency_widget",
 *   default_formatter = "se_currency_formatter",
 * )
 */
class CurrencyFieldType extends NumericItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Currency value'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   *
   */
  public function getConstraints() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'numeric',
          'precision' => 12,
        ],
      ],
    ];
  }

}
