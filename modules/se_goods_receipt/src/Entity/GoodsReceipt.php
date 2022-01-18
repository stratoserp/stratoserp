<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Goods Receipt entity.
 *
 * @ingroup se_goods_receipt
 *
 * @ContentEntityType(
 *   id = "se_goods_receipt",
 *   label = @Translation("Goods Receipt"),
 *   handlers = {
 *     "storage" = "Drupal\se_goods_receipt\GoodsReceiptStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_goods_receipt\GoodsReceiptListBuilder",
 *     "views_data" = "Drupal\se_goods_receipt\Entity\GoodsReceiptViewsData",
 *     "translation" = "Drupal\se_goods_receipt\GoodsReceiptTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_goods_receipt\Form\GoodsReceiptForm",
 *       "add" = "Drupal\se_goods_receipt\Form\GoodsReceiptForm",
 *       "edit" = "Drupal\se_goods_receipt\Form\GoodsReceiptForm",
 *       "delete" = "Drupal\se_goods_receipt\Form\GoodsReceiptDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_goods_receipt\GoodsReceiptHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_goods_receipt\GoodsReceiptAccessControlHandler",
 *   },
 *   base_table = "se_goods_receipt",
 *   data_table = "se_goods_receipt_field_data",
 *   revision_table = "se_goods_receipt_revision",
 *   revision_data_table = "se_goods_receipt_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer goods receipt entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/goods-receipt/{se_goods_receipt}",
 *     "add-form" = "/goods-receipt/add",
 *     "edit-form" = "/goods-receipt/{se_goods_receipt}/edit",
 *     "delete-form" = "/goods-receipt/{se_goods_receipt}/delete",
 *     "version-history" = "/goods-receipt/{se_goods_receipt}/revisions",
 *     "revision" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/view",
 *     "revision_revert" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/revert",
 *     "revision_delete" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/delete",
 *     "translation_revert" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/revert/{langcode}",
 *     "collection" = "/se/goods-receipt-list",
 *   },
 *   field_ui_base_route = "se_goods_receipt.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class GoodsReceipt extends StratosEntityBase implements GoodsReceiptInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'gr';
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_total->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Goods Receipt entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Goods Receipt entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 128)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    // Make the revision field configurable.
    // https://www.drupal.org/project/drupal/issues/2696555 will solve.
    $fields[$entity_type->getRevisionMetadataKey('revision_log_message')]->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
