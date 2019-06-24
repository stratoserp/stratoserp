<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldType\DynamicEntityReferenceItem;

/**
 * Plugin implementation of the 'se_item_line' field type.
 *
 * @FieldType(
 *   id = "se_payment_line",
 *   label = @Translation("Payment line"),
 *   description = @Translation("A payment line recorded from a customer.."),
 *   category = @Translation("Numeric"),
 *   list_class = "\Drupal\se_payment_line\Plugin\Field\FieldType\PaymentLineFieldItemList",
 *   default_widget = "se_payment_list_widget",
 *   default_formatter = "se_payment_list_formatter",
 * )
 */
class PaymentLineType extends DynamicEntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['quantity'] = [
      'type' => 'float',
      'not null' => FALSE,
    ];

    $schema['columns']['amount'] = [
      'description' => 'Payment amount.',
      'type' => 'int',
      'length' => 11,
      'not null' => TRUE,
      'default' => 0,
    ];

    $schema['columns']['date'] = [
      'description' => 'Date payment was received.',
      'type' => 'varchar',
      'length' => 20,
    ];

    $schema['columns']['invoice'] = [
      'description' => 'Invoice reference.',
      'type' => 'int',
    ];

    $schema['columns']['payment_type'] = [
      'description' => 'Payment type',
      'type' => 'int',
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

    $properties['amount'] = DataDefinition::create('integer')
      ->setLabel(t('Amount'))
      ->setRequired(TRUE);

    $properties['date'] = DataDefinition::create('string')
      ->setLabel(t('Date'))
      ->addConstraint('Date')
      ->setRequired(FALSE);

    $properties['invoice'] = DataDefinition::create('string')
      ->setLabel(t('Invoice'))
      ->setRequired(FALSE);

    $properties['payment_type'] = DataDefinition::create('string')
      ->setLabel(t('Payment type'))
      ->setRequired(FALSE);

    return $properties;
  }

}
