<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'se_item_line' field type.
 *
 * @FieldType(
 *   id = "se_payment_line",
 *   label = @Translation("Payment line"),
 *   description = @Translation("A payment line recorded from a customer."),
 *   category = @Translation("StratosERP"),
 *   list_class = "\Drupal\se_payment_line\Plugin\Field\FieldType\PaymentLineFieldItemList",
 *   default_widget = "se_payment_line_widget",
 *   default_formatter = "se_payment_line_formatter",
 * )
 */
class PaymentLineType extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['amount'] = [
      'description' => 'Payment amount.',
      'type' => 'int',
      'length' => 11,
      'not null' => TRUE,
      'default' => 0,
    ];

    $schema['columns']['payment_date'] = [
      'description' => 'Date payment was received.',
      'type' => 'varchar',
      'length' => 20,
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

    $properties['amount'] = DataDefinition::create('integer')
      ->setLabel(t('Amount'))
      ->setRequired(TRUE);

    $properties['payment_date'] = DataDefinition::create('string')
      ->setLabel(t('Date'))
      ->setRequired(FALSE);

    $properties['date'] = DataDefinition::create('any')
      ->setLabel(t('Computed date'))
      ->setDescription(t('The computed DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'payment_date');

    $properties['payment_type'] = DataDefinition::create('string')
      ->setLabel(t('Payment type'))
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
