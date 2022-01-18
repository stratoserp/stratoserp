<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Purchase Order entity.
 *
 * @ingroup se_purchase_order
 *
 * @ContentEntityType(
 *   id = "se_purchase_order",
 *   label = @Translation("Purchase Order"),
 *   handlers = {
 *     "storage" = "Drupal\se_purchase_order\PurchaseOrderStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_purchase_order\PurchaseOrderListBuilder",
 *     "views_data" = "Drupal\se_purchase_order\Entity\PurchaseOrderViewsData",
 *     "translation" = "Drupal\se_purchase_order\PurchaseOrderTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_purchase_order\Form\PurchaseOrderForm",
 *       "add" = "Drupal\se_purchase_order\Form\PurchaseOrderForm",
 *       "edit" = "Drupal\se_purchase_order\Form\PurchaseOrderForm",
 *       "delete" = "Drupal\se_purchase_order\Form\PurchaseOrderDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_purchase_order\PurchaseOrderHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_purchase_order\PurchaseOrderAccessControlHandler",
 *   },
 *   base_table = "se_purchase_order",
 *   data_table = "se_purchase_order_field_data",
 *   revision_table = "se_purchase_order_revision",
 *   revision_data_table = "se_purchase_order_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer purchase order entities",
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
 *     "canonical" = "/purchase-order/{se_purchase_order}",
 *     "add-form" = "/purchase-order/add",
 *     "edit-form" = "/purchase-order/{se_purchase_order}/edit",
 *     "delete-form" = "/purchase-order/{se_purchase_order}/delete",
 *     "version-history" = "/purchase-order/{se_purchase_order}/revisions",
 *     "revision" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/view",
 *     "revision_revert" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/revert",
 *     "revision_delete" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/delete",
 *     "translation_revert" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/revert/{langcode}",
 *     "collection" = "/se/purchase-order-list",
 *   },
 *   field_ui_base_route = "se_purchase_order.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class PurchaseOrder extends StratosEntityBase implements PurchaseOrderInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'po';
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
      ->setDescription(t('The user ID of author of the PurchaseOrder entity.'))
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
      ->setDescription(t('The name of the PurchaseOrder entity.'))
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
