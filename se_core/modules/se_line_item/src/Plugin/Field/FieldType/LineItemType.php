<?php

namespace Drupal\se_line_item\Plugin\Field\FieldType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldType\DynamicEntityReferenceItem;

/**
 * Plugin implementation of the 'se_line_item' field type.
 *
 * @FieldType(
 *   id = "se_line_item",
 *   label = @Translation("Line item reference"),
 *   description = @Translation("A line item dynamic entity reference.."),
 *   category = @Translation("Dynamic Reference"),
 *   list_class = "\Drupal\se_line_item\Plugin\Field\FieldType\LineItemFieldItemList",
 *   default_widget = "se_line_item_widget",
 *   default_formatter = "se_line_item_formatter",
 * )
 */
class LineItemType extends DynamicEntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['quantity'] = [
      'type' => 'float',
      'not null' => FALSE,
    ];

    $schema['columns']['price'] = [
      'description' => 'Price that the item is selling for.',
      'type' => 'int',
      'length' => 11,
      'not null' => TRUE,
      'default' => 0,
    ];

    $schema['columns']['note'] = [
      'description' => 'Notes.',
      'type' => 'text',
      'size' => 'big',
      'not null' => TRUE,
      'default' => '',
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['quantity'] = DataDefinition::create('float')
      ->setLabel(t('Quantity'));

    $properties['price'] = DataDefinition::create('integer')
      ->setLabel(t('Price'));

    $properties['note'] = DataDefinition::create('string')
      ->setLabel(t('Note'));

    return $properties;
  }

}
