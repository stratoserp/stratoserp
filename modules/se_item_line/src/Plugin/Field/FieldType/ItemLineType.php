<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldType\DynamicEntityReferenceItem;

/**
 * Plugin implementation of the 'se_item_line' field type.
 *
 * @FieldType(
 *   id = "se_item_line",
 *   label = @Translation("Item line"),
 *   description = @Translation("An item line extension to the dynamic entity reference field."),
 *   category = @Translation("StratosERP"),
 *   list_class = "\Drupal\se_item_line\Plugin\Field\FieldType\ItemLineFieldItemList",
 *   default_widget = "se_item_line_widget",
 *   default_formatter = "se_item_line_formatter",
 * )
 */
class ItemLineType extends DynamicEntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['quantity'] = [
      'type' => 'float',
      'not null' => TRUE,
    ];

    $schema['columns']['price'] = [
      'description' => 'Price that the item is selling for.',
      'type' => 'int',
      'length' => 11,
      'not null' => TRUE,
      'default' => 0,
    ];

    $schema['columns']['cost'] = [
      'description' => 'Cost of the item.',
      'type' => 'int',
      'length' => 11,
      'not null' => TRUE,
      'default' => 0,
    ];

    $schema['columns']['serial'] = [
      'description' => 'Serial number for the item.',
      'type' => 'varchar_ascii',
      'not null' => FALSE,
      'length' => 255,
    ];

    $schema['columns']['completed_date'] = [
      'description' => 'Date work was completed.',
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => 20,
    ];

    $schema['columns']['note'] = [
      'description' => 'Notes storage.',
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
    ];

    $schema['columns']['format'] = [
      'description' => 'Format storage for notes.',
      'type' => 'varchar_ascii',
      'not null' => FALSE,
      'length' => 255,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['quantity'] = DataDefinition::create('float')
      ->setLabel(t('Quantity'))
      ->setRequired(TRUE);

    $properties['price'] = DataDefinition::create('string')
      ->setLabel(t('Price'))
      ->setRequired(TRUE);

    $properties['cost'] = DataDefinition::create('string')
      ->setLabel(t('Cost'))
      ->setRequired(TRUE);

    $properties['serial'] = DataDefinition::create('string')
      ->setLabel(t('Serial'))
      ->setRequired(FALSE);

    $properties['completed_date'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Completed date'))
      ->setRequired(FALSE);

    $properties['date'] = DataDefinition::create('any')
      ->setLabel(t('Computed date'))
      ->setDescription(t('The computed DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'completed_date')
      ->setRequired(FALSE);

    $properties['note'] = DataDefinition::create('string')
      ->setLabel(t('Note'))
      ->setRequired(FALSE);

    $properties['format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'))
      ->setRequired(FALSE);

    $properties['processed'] = DataDefinition::create('string')
      ->setLabel(t('Processed text'))
      ->setDescription(t('The text with the text format applied.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\text\TextProcessed')
      ->setSetting('text source', 'note')
      ->setInternal(FALSE)
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    return [];
  }

}
